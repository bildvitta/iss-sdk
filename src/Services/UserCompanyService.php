<?php

namespace BildVitta\Hub\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class UserCompanyService
{
    const MANAGER = 0;

    const SUPERVISOR = 1;

    const BROKER = 2;

    /**
     * Limpa o cache relacionado a um UserCompany específico
     */
    public static function clearCacheByUserCompany(Model $userCompany): void
    {
        Cache::tags(['UserCompanyService', "User-{$userCompany->user->uuid}"])->flush();
    }

    /**
     * Limpa o cache da classe UserCompanyService
     */
    public static function clearCache(): void
    {
        Cache::tags(['UserCompanyService'])->flush();
    }

    /**
     * Obtém usuários subordinados a um usuário pai
     */
    public static function getUsersByParentUuid(
        string $parentUserUuid,
        string $positionUuid,
        string $companyUuid,
        bool $allBelow = false,
        ?int $onlyPositionOrder = null,
        array $attributes = ['uuid', 'name', 'is_active']
    ): Collection {
        $cacheKey = self::generateCacheKey('UsersByParentUuid', $parentUserUuid, $positionUuid, $companyUuid, $allBelow, $onlyPositionOrder, $attributes);

        try {
            // Usar cache para evitar processamento desnecessário
            if (Cache::tags(['UserCompanyService', "User-$parentUserUuid"])->has($cacheKey)) {
                return Cache::tags(['UserCompanyService', "User-$parentUserUuid"])->get($cacheKey);
            }

            $parentUser = self::getUserByUuid($parentUserUuid);
            if (! $parentUser) {
                return collect([]);
            }

            $position = self::getPositionByUuid($positionUuid);
            if (! $position) {
                return collect([]);
            }

            $company = self::getCompanyByUuid($companyUuid);
            if (! $company) {
                return collect([]);
            }

            $userCompany = self::getUserCompany($parentUser, $company, $position);
            if (! $userCompany) {
                return collect([]);
            }

            $positionsByCompany = null;
            if ($onlyPositionOrder !== null) {
                $sortedPositions = self::getSortedPositions($companyUuid);
                $positionsByCompany = $sortedPositions[$onlyPositionOrder] ?? null;
                if (! $positionsByCompany) {
                    return collect([]);
                }
            }

            $userCompanyParents = $userCompany->user_company_parent()->get();
            $userChildIds = [];

            if ($allBelow) {
                $userChildIds = self::collectAllUserChildrens($userCompanyParents, $onlyPositionOrder, $positionsByCompany);
            } else {
                $userChildIds = self::collectUserChildrens($userCompanyParents);
            }

            return Cache::tags(['UserCompanyService', "User-$parentUserUuid"])->remember(
                $cacheKey,
                now()->addHour(),
                fn () => self::getUserModel()::whereIn('id', $userChildIds)->get($attributes)
            );
        } catch (\Exception $e) {
            report($e);

            return collect([]);
        }
    }

    /**
     * Coleta os filhos diretos de usuários sem usar variáveis estáticas
     */
    private static function collectUserChildrens(Collection $userCompanyParents): array
    {
        $userChildIds = [];

        foreach ($userCompanyParents as $userCompanyParent) {
            $userCompanyChildren = $userCompanyParent->user_company_children()->select('user_id')->first();

            if (! $userCompanyChildren) {
                throw new \Exception('Falha ao buscar usuários de cargo filho!');
            }

            $userChildIds[] = $userCompanyChildren->user_id;
        }

        return $userChildIds;
    }

    /**
     * Coleta todos os filhos recursivamente sem usar variáveis estáticas
     */
    private static function collectAllUserChildrens(Collection $userCompanyParents, ?int $onlyPositionOrder, ?array $positionsByCompany): array
    {
        $userChildIds = [];
        self::getAllUserChildrensRecursive($userCompanyParents, $onlyPositionOrder, $positionsByCompany, $userChildIds);

        return $userChildIds;
    }

    /**
     * Implementação recursiva para coletar todos os filhos
     */
    private static function getAllUserChildrensRecursive(
        Collection $userCompanyParents,
        ?int $onlyPositionOrder,
        ?array $positionsByCompany,
        array &$userChildIds
    ): void {
        $childrensParents = collect([]);

        foreach ($userCompanyParents as $userCompanyParent) {
            $userCompanyChildren = $userCompanyParent
                ->user_company_children()
                ->select('id', 'user_id', 'position_id')
                ->first();

            if (! $userCompanyChildren) {
                throw new \Exception('Falha ao buscar todos os usuários de cargo filho!');
            }

            if ($onlyPositionOrder === null ||
                ($userCompanyChildren->position_id && $positionsByCompany['id'] == $userCompanyChildren->position_id)) {
                $userChildIds[] = $userCompanyChildren->user_id;
            }

            $userChildrenIsParent = $userCompanyChildren->user_company_parent()->get();

            if ($userChildrenIsParent->isNotEmpty()) {
                $childrensParents = $childrensParents->merge($userChildrenIsParent);
            }
        }

        if ($childrensParents->isNotEmpty()) {
            self::getAllUserChildrensRecursive($childrensParents, $onlyPositionOrder, $positionsByCompany, $userChildIds);
        }
    }

    /**
     * Obtém todos os pais de um usuário
     */
    public static function getAllParentsByUserUuid(string $userUuid, string $companyUuid, bool $onlyTop = false): Collection
    {
        $cacheKey = self::generateCacheKey('AllParentsByUserUuid', $userUuid, $companyUuid, $onlyTop);

        try {
            if (Cache::tags(['UserCompanyService', "User-$userUuid"])->has($cacheKey)) {
                return Cache::tags(['UserCompanyService', "User-$userUuid"])->get($cacheKey);
            }

            $user = self::getUserByUuid($userUuid);
            if (! $user) {
                return collect([]);
            }

            $company = self::getCompanyByUuid($companyUuid);
            if (! $company) {
                return collect([]);
            }

            $userCompanyChildren = $user->user_companies
                ->where('company_id', $company->id)
                ->first()
                ?->user_company_children()
                ?->first();

            if (! $userCompanyChildren) {
                return collect([]);
            }

            $userParents = [];
            self::collectAllUserParents($userCompanyChildren, null, $userParents);

            if ($onlyTop && ! empty($userParents)) {
                $topUserParent = end($userParents);
                $userParents = [$topUserParent];
            }

            return Cache::tags(['UserCompanyService', "User-$userUuid"])->remember(
                $cacheKey,
                now()->addHour(),
                fn () => collect($userParents)->reverse()
            );
        } catch (\Exception $e) {
            report($e);

            return collect([]);
        }
    }

    /**
     * Coleta recursivamente todos os pais de um usuário sem usar variáveis estáticas
     */
    private static function collectAllUserParents(
        Model $userCompanyChildren,
        ?int $onlyPositionOrder = null,
        array &$userParents = []
    ): void {
        $userCompanyParent = $userCompanyChildren->user_company_parent()->first();

        if (! $userCompanyParent) {
            throw new \Exception('Falha ao buscar todos os usuários de cargo pai!');
        }

        if ($onlyPositionOrder === null ||
            ($userCompanyParent->position_id && isset(self::$positionsByCompany['id']) &&
                self::$positionsByCompany['id'] == $userCompanyParent->position_id)) {
            $userParents[] = $userCompanyParent;
        }

        $nextUserCompanyChildren = $userCompanyParent->user_company_children()->first();

        if ($nextUserCompanyChildren) {
            self::collectAllUserParents($nextUserCompanyChildren, $onlyPositionOrder, $userParents);
        }
    }

    /**
     * Verifica a posição do usuário na empresa
     */
    public static function checkPositionUser(string $companyUuid, string $userUuid, int $positionOrder): bool
    {
        $cacheKey = self::generateCacheKey('CheckPositionUser', $companyUuid, $userUuid, $positionOrder);

        if (Cache::tags(['UserCompanyService', "User-$userUuid"])->has($cacheKey)) {
            return Cache::tags(['UserCompanyService', "User-$userUuid"])->get($cacheKey);
        }

        $position = self::getSortedPositions($companyUuid)[$positionOrder];
        $userCompany = self::getUserCompanyByUuidAndCompany($userUuid, $companyUuid);

        if (! $userCompany) {
            return false;
        }

        return Cache::tags(['UserCompanyService', "User-$companyUuid"])->remember(
            $cacheKey,
            now()->addHour(),
            fn () => $userCompany->position_id == $position['id']
        );
    }

    /**
     * Obtém usuários por empresa e ordem de posição
     */
    public static function getUsersByCompanyUuidAndPositionOrder(
        string $companyUuid,
        int $positionOrder,
        array $filter = ['is_active' => 1],
        array $attributes = ['uuid', 'name', 'is_active']
    ): Collection {
        $cacheKey = self::generateCacheKey('UsersByCompanyUuidAndPositionOrder', $companyUuid, $positionOrder, $filter, $attributes);

        if (Cache::tags(['UserCompanyService', "Company-$companyUuid"])->has($cacheKey)) {
            return Cache::tags(['UserCompanyService', "Company-$companyUuid"])->get($cacheKey);
        }

        $company = self::getCompanyByUuid($companyUuid);
        if (! $company || empty($attributes)) {
            return collect([]);
        }

        $position = self::getSortedPositions($companyUuid)[$positionOrder];

        $users = self::getUserModel()::whereHas('user_companies', function ($query) use ($company, $position) {
            $query->where('company_id', $company->id)
                ->where('position_id', $position['id']);
        })
            ->select($attributes)
            ->orderBy('name');

        foreach ($filter as $key => $value) {
            $users->where("users.$key", $value);
        }

        $users = $users->get();

        return Cache::tags(['UserCompanyService', "Company-$companyUuid"])->remember(
            $cacheKey,
            now()->addHour(),
            fn () => $users
        );
    }

    /**
     * Obtém o usuário pai direto
     */
    public static function getParentUser(string $userUuid, string $companyUuid, array $attributes = ['uuid', 'name', 'is_active']): Collection
    {
        $cacheKey = self::generateCacheKey('ParentUser', $userUuid, $companyUuid, $attributes);

        try {
            if (Cache::tags(['UserCompanyService', "User-$userUuid"])->has($cacheKey)) {
                return Cache::tags(['UserCompanyService', "User-$userUuid"])->get($cacheKey);
            }

            $user = self::getUserByUuid($userUuid);
            if (! $user) {
                return collect([]);
            }

            $company = self::getCompanyByUuid($companyUuid);
            if (! $company) {
                return collect([]);
            }

            $userCompany = $user->user_companies
                ->where('company_id', $company->id)
                ->where('is_seller', true)
                ->first();

            if (! $userCompany || ! $userCompany->user_company_children) {
                return collect([]);
            }

            $userParent = $userCompany->user_company_children->user_company_parent->user ?? null;

            if (! $userParent) {
                return collect([]);
            }

            return Cache::tags(['UserCompanyService', "User-$userUuid"])->remember(
                $cacheKey,
                now()->addHour(),
                fn () => self::getUserModel()::where('id', $userParent->id)->get($attributes)
            );
        } catch (\Exception $e) {
            report($e);

            return collect([]);
        }
    }

    /**
     * Obtém as posições ordenadas por empresa
     */
    public static function getSortedPositions(string $companyUuid): array
    {
        $cacheKey = "sorted_positions_$companyUuid";

        return Cache::tags(['UserCompanyService', "Company-$companyUuid"])->remember(
            $cacheKey,
            now()->addHour(),
            function () use ($companyUuid) {
                $positions = [];

                $company = self::getCompanyByUuid($companyUuid);
                if (! $company) {
                    return [];
                }

                $companyId = $company->main_company_id ?? $company->id;

                $allPositions = app(config('hub.model_position'))
                    ->where('company_id', $companyId)
                    ->get()->toArray();

                if (! $allPositions) {
                    return [];
                }

                self::sortPositionsToArray($allPositions, $positions);

                return $positions;
            }
        );
    }

    /**
     * Ordena as posições hierarquicamente sem usar variáveis estáticas
     */
    private static function sortPositionsToArray(array $allPositions, array &$sortedPositions): void
    {
        if (empty($allPositions)) {
            return;
        }

        $remainingPositions = $allPositions;

        // Adiciona posições sem pai primeiro
        foreach ($allPositions as $key => $position) {
            if (! $position['parent_position_id']) {
                $sortedPositions[] = $position;
                unset($remainingPositions[$key]);
            }
        }

        // Adiciona posições cujo pai é a última posição adicionada
        foreach ($remainingPositions as $key => $position) {
            $lastPosition = end($sortedPositions);
            if ($position['parent_position_id'] == $lastPosition['id']) {
                $sortedPositions[] = $position;
                unset($remainingPositions[$key]);
            }
        }

        // Continua recursivamente se ainda houver posições não ordenadas
        if (! empty($remainingPositions)) {
            self::sortPositionsToArray($remainingPositions, $sortedPositions);
        }
    }

    // Métodos auxiliares permaneceriam os mesmos...
    private static function generateCacheKey(string $prefix, ...$params): string
    {
        return "UCS-$prefix-".implode(':', array_map(
            fn ($param) => is_array($param) ? json_encode($param) : $param,
            array_filter($params, fn ($param) => ! is_null($param))
        ));
    }

    private static function getUserByUuid(string $uuid): ?Model
    {
        $modelUserKey = config('hub.model_user_key');

        return self::getUserModel()::with('user_companies')->where($modelUserKey, $uuid)->first();
    }

    private static function getPositionByUuid(string $uuid): ?Model
    {
        return app(config('hub.model_position'))::where('uuid', $uuid)->first();
    }

    private static function getCompanyByUuid(string $uuid): ?Model
    {
        return app(config('hub.model_company'))::where('uuid', $uuid)->first();
    }

    private static function getUserCompany(Model $user, Model $company, Model $position): ?Model
    {
        return $user->user_companies()
            ->with('user_company_parent')
            ->where('company_id', $company->id)
            ->where('position_id', $position->id)
            ->first();
    }

    private static function getUserCompanyByUuidAndCompany(string $userUuid, string $companyUuid): ?Model
    {
        $modelUserKey = config('hub.model_user_key');

        return app(config('hub.model_user_company'))::join('users', 'user_companies.user_id', 'users.id')
            ->join('companies', 'companies.id', 'user_companies.company_id')
            ->where('companies.uuid', $companyUuid)
            ->where("users.$modelUserKey", $userUuid)
            ->select('user_companies.position_id')
            ->first();
    }

    private static function getUserModel(): string
    {
        return app(config('hub.model_user'))->getMorphClass();
    }
}
