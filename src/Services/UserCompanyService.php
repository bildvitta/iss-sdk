<?php

namespace BildVitta\Hub\Services;

use App\Models\Company;
use BildVitta\Hub\Entities\Position;
use App\Models\User;
use BildVitta\Hub\Entities\UserCompanyParentPosition;
use Cache;

class UserCompanyService
{
    private static $userChildrens = [];

    public static function getUsersByParentId($parentUserUuid, $positionUuid, $companyUuid, $allBelow = false)
    {
        $allBelowCache = $allBelow ? 'all-bellow-true' : 'all-bellow-false';
        $cacheKey = "team-{$companyUuid}-{$parentUserUuid}-{$positionUuid}-{$allBelowCache}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $parentUser = User::with('user_companies')
            ->where('uuid', $parentUserUuid)->first();

        if (! $parentUser) {
            return collect([]);
        }

        $position = Position::where('uuid', $positionUuid)->first();

        if (! $position) {
            return collect([]);
        }

        $company = Company::where('uuid', $companyUuid)->first();

        if (! $company) {
            return collect([]);
        }

        $userCompany = $parentUser->user_companies()
            ->with('user_company_parent')
            ->where('company_id', $company->id)
            ->where('position_id', $position->id)
            ->first();

        if (! $userCompany) {
            return collect([]);
        }

        $userCompanyParents = $userCompany->user_company_parent()->get();

        if (! $allBelow) {
            self::getUserChildrens($userCompanyParents);
        }

        if ($allBelow) {
            self::getAllUserChildrens($userCompanyParents);
        }

        return Cache::remember($cacheKey, now()->addHour(), function () {
            return User::whereIn('id', self::$userChildrens)
                ->get(['uuid', 'name']);
        });
    }

    private static function getUserChildrens($userCompanyParents)
    {
        foreach ($userCompanyParents as $userCompanyParent) {
            self::$userChildrens[] = $userCompanyParent
                ->user_company_children()
                ->select('user_id')
                ->first()
                ->user_id;
        }
    }

    private static function getAllUserChildrens($userCompanyParents)
    {
        $childrensParents = [];

        foreach ($userCompanyParents as $userCompanyParent) {

            $userCompanyChildren = $userCompanyParent
                ->user_company_children()
                ->select('id', 'user_id')
                ->first();

            self::$userChildrens[] = $userCompanyChildren->user_id;

            $userChildrenIsParent = UserCompanyParentPosition::isParent($userCompanyChildren->id);

            if ($userChildrenIsParent) {
                $childrensParents[] = $userChildrenIsParent;
            }
        }

        if (count($childrensParents)) {
            self::getAllUserChildrens($childrensParents);
        }
    }
}
