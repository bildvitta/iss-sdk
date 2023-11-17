<?php

namespace BildVitta\Hub\Services;

use Illuminate\Support\Facades\Cache;

class UserCompanyService
{
    private static $userChildrens = [];
    private static $userParents = [];
    private static $positions = [];

    public static function clearCacheByUserCompany($userCompany)
    {
        Cache::tags(["UserCompanyService", "User-{$userCompany->user->uuid}"])->flush();
    }

    /**
     * Get users below parent user
     * 
     * @param string $parentUserUuid
     * @param string $parentUserUuid
     * @param string $companyUuid
     * @param boolean $allBelow
     * @param array $attributes
     * @return User
     * 
     */
    public static function getUsersByParentUuid($parentUserUuid, $positionUuid, $companyUuid, $allBelow = false, $attributes = ["uuid", "name", "is_active"])
    {
        $cacheKey = "UCS-UsersByParentUuid-$parentUserUuid-$positionUuid-$companyUuid-" . ($allBelow ? "true" : "false") . "-" . implode("-", $attributes);

        try {
            self::$userChildrens = [];
            self::$userParents = [];

            if (Cache::tags(["UserCompanyService", "User-$parentUserUuid"])->has($cacheKey)) {
                return Cache::tags(["UserCompanyService", "User-$parentUserUuid"])->get($cacheKey);
            }

            $modelUserKey = config("hub.model_user_key");

            $userModel = app(config("hub.model_user"));
            $parentUser = $userModel::with("user_companies")
                ->where($modelUserKey, $parentUserUuid)->first();

            if (!$parentUser) {
                return collect([]);
            }

            $positionModel = app(config("hub.model_position"));
            $position = $positionModel::where("uuid", $positionUuid)->first();

            if (!$position) {
                return collect([]);
            }

            $companyModel = app(config("hub.model_company"));
            $company = $companyModel::where("uuid", $companyUuid)->first();

            if (!$company) {
                return collect([]);
            }

            $userCompany = $parentUser->user_companies()
                ->with("user_company_parent")
                ->where("company_id", $company->id)
                ->where("position_id", $position->id)
                ->first();

            if (!$userCompany) {
                return collect([]);
            }

            $userCompanyParents = $userCompany->user_company_parent()->get();

            if (!$allBelow) {
                self::getUserChildrens($userCompanyParents);
            }

            if ($allBelow) {
                self::getAllUserChildrens($userCompanyParents);
            }

            return Cache::tags(["UserCompanyService", "User-$parentUserUuid"])->remember($cacheKey, now()->addHour(), function () use ($userModel, $attributes) {
                return $userModel::whereIn("id", self::$userChildrens)
                    ->get($attributes);
            });
        } catch (\Exception $e) {
            report($e);
            return collect([]);
        }
    }

    private static function getUserChildrens($userCompanyParents)
    {
        foreach ($userCompanyParents as $userCompanyParent) {
            $userCompanyChildren = $userCompanyParent
                ->user_company_children()
                ->select("user_id")
                ->first();

            if (!$userCompanyChildren) {
                throw new \Exception("Falha ao buscar usuários de cargo filho!");
            }

            self::$userChildrens[] = $userCompanyChildren->user_id;
        }
    }

    private static function getAllUserChildrens($userCompanyParents)
    {
        $childrensParents = collect([]);

        foreach ($userCompanyParents as $userCompanyParent) {

            $userCompanyChildren = $userCompanyParent
                ->user_company_children()
                ->select("id", "user_id")
                ->first();

            if (!$userCompanyChildren) {
                throw new \Exception("Falha ao buscar todos os usuários de cargo filho!");
            }

            self::$userChildrens[] = $userCompanyChildren->user_id;

            $userChildrenIsParent = $userCompanyChildren->user_company_parent()->get();

            if (count($userChildrenIsParent)) {
                $childrensParents = $childrensParents->merge($userChildrenIsParent);
            }
        }

        if (count($childrensParents)) {
            self::getAllUserChildrens($childrensParents);
        }
    }

    public static function getAllParentsByUserUuid($userUuid, $companyUuid, $onlyTop = false)
    {
        $cacheKey = "UCS-AllParentsByUserUuid-$userUuid-$companyUuid-" . ($onlyTop ? "true" : "false");

        try {
            self::$userChildrens = [];
            self::$userParents = [];

            if (Cache::tags(["UserCompanyService", "User-$userUuid"])->has($cacheKey)) {
                return Cache::tags(["UserCompanyService", "User-$userUuid"])->get($cacheKey);
            }

            $userModel = app(config("hub.model_user"));
            $user = $userModel::with("user_companies")
                ->where("uuid", $userUuid)->first();

            if (!$user) {
                return collect([]);
            }

            $companyModel = app(config("hub.model_company"));
            $company = $companyModel::where("uuid", $companyUuid)->first();

            if (!$company) {
                return collect([]);
            }

            $userCompanyChildren = $user->user_companies
                ->where("company_id", $company->id)
                ->first()
                ->user_company_children()
                ->first();

            if (!$userCompanyChildren) {
                return collect([]);
            }

            self::getAllUserParentsByUserCompanyChildren($userCompanyChildren);

            if ($onlyTop) {
                $topUserId = end(self::$userParents);
                self::$userParents = [];
                self::$userParents[] = $topUserId;
            }

            return Cache::tags(["UserCompanyService", "User-$userUuid"])->remember($cacheKey, now()->addHour(), function () {
                return collect(self::$userParents)->reverse();
            });
        } catch (\Exception $e) {
            report($e);
            return collect([]);
        }
    }

    private static function getAllUserParentsByUserCompanyChildren($userCompanyChildren)
    {
        $userCompanyParent = $userCompanyChildren->user_company_parent()
            ->first();

        if (!$userCompanyParent) {
            throw new \Exception("Falha ao buscar todos os usuários de cargo pai!");
        }

        self::$userParents[] = $userCompanyParent;

        $userCompanyChildren = $userCompanyParent->user_company_children()
            ->first();

        if ($userCompanyChildren) {
            self::getAllUserParentsByUserCompanyChildren($userCompanyChildren);
        }
    }

    /**
     * Get users by companyUuid and positionUuid
     * 
     * @param string $companyUuid
     * @param int $positionOrder 0|1|2
     * @param array $filter
     * @param array $attributes
     *
     */
    public static function getUsersByCompanyUuidAndPositionOrder($companyUuid, $positionOrder, $filter = ['is_active' => 1], $attributes = ['uuid', 'name', 'is_active'])
    {
        $cacheKey = "UCS-UsersByCompanyUuidAndPositionOrder-{$companyUuid}-{$positionOrder}-filter-" . implode("-", $filter) . "-attributes-" . implode("-", $attributes);

        if (Cache::tags(["UserCompanyService", "Company-$companyUuid"])->has($cacheKey)) {
            return Cache::tags(["UserCompanyService", "Company-$companyUuid"])->get($cacheKey);
        }

        $companyModel = app(config("hub.model_company"));
        $company = $companyModel::where("uuid", $companyUuid)->first();

        if (!$company) {
            return collect([]);
        }

        if (!count($attributes)) {
            return collect([]);
        }

        $position = self::getSortedPositions($companyUuid)[$positionOrder];

        $userCompanyModel = app(config("hub.model_user_company"));
        $userModel = app(config("hub.model_user"));
        $tableUser = $userModel->getTable();
        $tableUserCompany = $userCompanyModel->getTable();

        if($attributes && count($attributes)) {
            $attributes = collect($attributes)->map(function($item) use($tableUser) {
                return $tableUser . '.' . $item;
            });
        }

        $users = $userModel::join($tableUserCompany, "{$tableUserCompany}.user_id", "{$tableUser}.id")
            ->where("{$tableUserCompany}.company_id", $company->id)
            ->where("{$tableUserCompany}.position_id", $position['id'])
            ->select($attributes->toArray())
            ->orderBy('name');

        if(count($filter)) {
            foreach($filter as $key=>$value) {
                $users->where("{$tableUser}." . $key, $value);
            }
        }

        $users = $users->get();

        return Cache::tags(["UserCompanyService", "Company-$companyUuid"])->remember($cacheKey, now()->addHour(), function () use ($users) {
            return $users;
        });
    }

    public static function getSortedPositions($companyUuid)
    {
        self::$positions = [];
        
        $positionModel = app(config("hub.model_position"));
        $companyModel = app(config("hub.model_company"));

        $company = $companyModel::whereUuid($companyUuid)->first();

        if (!$company) {
            return [];
        }

        $companyId = $company->main_company_id ?? $company->id;

        $positions = $positionModel->where("company_id", $companyId)
            ->get()
            ->toArray();

        if (!$positions) {
            return [];
        }

        self::sortPositions($positions);

        return self::$positions;
    }

    private static function sortPositions($positions)
    {
        if (!count($positions)) {
            return;
        }

        foreach ($positions as $key => $position) {
            if (!$position["parent_position_id"]) {
                self::$positions[] = $position;
                unset($positions[$key]);
            }
        }

        foreach ($positions as $key => $position) {
            $lastPosition = end(self::$positions);
            if ($position["parent_position_id"] == $lastPosition["id"]) {
                self::$positions[] = $position;
                unset($positions[$key]);
            }
        }

        if (count($positions)) {
            self::sortPositions($positions);
        }
    }

    /**
     * Check position user in company
     * 
     * @param string $companyUuid
     * @param string $userUuid
     * @param int $positionOrder 0|1|2
     * @return boolean
     * 
     */
    public static function checkPositionUser($companyUuid, $userUuid, $positionOrder)
    {
        $cacheKey = "UCS-CheckPositionUser-{$companyUuid}-{$userUuid}-{$positionOrder}";

        if (Cache::tags(["UserCompanyService", "User-$userUuid"])->has($cacheKey)) {
            return Cache::tags(["UserCompanyService", "User-$userUuid"])->get($cacheKey);
        }

        $position = self::getSortedPositions($companyUuid)[$positionOrder];

        $userCompanyModel = app(config("hub.model_user_company"));
        $userModel = app(config("hub.model_user"));
        $companyModel = app(config("hub.model_company"));
        $tableUser = $userModel->getTable();
        $tableUserCompany = $userCompanyModel->getTable();
        $tableCompany = $companyModel->getTable();
        $modelUserKey = config("hub.model_user_key");
        
        $userCompany = $userCompanyModel::join($tableUser, "{$tableUserCompany}.user_id", "{$tableUser}.id")
            ->join($tableCompany, "{$tableCompany}.id", "{$tableUserCompany}.company_id")
            ->where("{$tableCompany}.uuid", $companyUuid)
            ->where("{$tableUser}.{$modelUserKey}", $userUuid)
            ->select(["{$tableUserCompany}.position_id"])
            ->first();

        if(!$userCompany) {
            return false;
        }

        return Cache::tags(["UserCompanyService", "User-$companyUuid"])->remember($cacheKey, now()->addHour(), function () use ($userCompany, $position) {
            return $userCompany->toArray()['position_id'] == $position['id'];
        });        

    }
}
