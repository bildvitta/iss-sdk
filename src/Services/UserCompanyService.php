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

    public static function getUsersByParentUuid($parentUserUuid, $positionUuid, $companyUuid, $allBelow = false)
    {
        $cacheKey = "UCS-UsersByParentUuid-$parentUserUuid-$positionUuid-$companyUuid-" . ($allBelow ? "true" : "false");

        try {
            self::$userChildrens = [];
            self::$userParents = [];

            if (Cache::tags(["UserCompanyService", "User-$parentUserUuid"])->has($cacheKey)) {
                return Cache::tags(["UserCompanyService", "User-$parentUserUuid"])->get($cacheKey);
            }

            $userModel = app(config("hub.model_user"));
            $parentUser = $userModel::with("user_companies")
                ->where("uuid", $parentUserUuid)->first();

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

            return Cache::tags(["UserCompanyService", "User-$parentUserUuid"])->remember($cacheKey, now()->addHour(), function () use ($userModel) {
                return $userModel::whereIn("id", self::$userChildrens)
                    ->get(["uuid", "name", "is_active"]);
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

    public static function getAllTopParentUsersByCompanyUuid($companyUuid)
    {
        $cacheKey = "UCS-AllTopParentUsersByCompanyUuid-$companyUuid";

        if (Cache::tags(["UserCompanyService", "Company-$companyUuid"])->has($cacheKey)) {
            return Cache::tags(["UserCompanyService", "Company-$companyUuid"])->get($cacheKey);
        }

        $companyModel = app(config("hub.model_company"));
        $company = $companyModel::where("uuid", $companyUuid)->first();


        if (!$company) {
            return collect([]);
        }

        $userCompanyModel = app(config("hub.model_user_company"));
        $userCompany = $userCompanyModel::doesntHave("user_company_children")
            ->with("user")
            ->where("company_id", $company->id)
            ->get();

        $users = collect([]);

        $userCompany->each(function ($item) use ($users) {
            $users[] = [
                "uuid" => $item->user->uuid,
                "name" => $item->user->name
            ];
        });

        return Cache::tags(["UserCompanyService", "Company-$companyUuid"])->remember($cacheKey, now()->addHour(), function () use ($users) {
            return $users;
        });
    }

    public static function getPositionsByOrder($companyUuid, $order = 0)
    {
        self::$positions = [];

        $cacheKey = "UCS-PositionsByOrder-$companyUuid-order-" . $order;

        if (Cache::tags(["UserCompanyService", "UserCompany-$companyUuid"])->has($cacheKey)) {
            return Cache::tags(["UserCompanyService", "UserCompany-$companyUuid"])->get($cacheKey);
        }

        $positionModel = app(config("hub.model_position"));
        $companyModel = app(config("hub.model_company"));
        $userCompanyModel = app(config("hub.model_user_company"));

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

        if (!array_key_exists($order, $positions)) {
            return [];
        }

        $position = self::$positions[$order];

        return Cache::tags(["UserCompanyService", "UserCompany-$companyUuid"])->remember($cacheKey, now()->addHour(), function () use ($userCompanyModel, $company, $position) {
            return $userCompanyModel->with(["user", "position", "company"])
                ->where("company_id", $company->id)
                ->where("position_id", $position["id"])
                ->get()
                ->toArray();
            ;
        });
    }

    public function getSortedPositions($companyUuid)
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
}
