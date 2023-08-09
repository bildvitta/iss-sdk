<?php

namespace BildVitta\Hub\Services;

use BildVitta\Hub\Entities\Position;
use App\Models\User;
use BildVitta\Hub\Entities\UserCompanyParentPosition;
use BildVitta\Hub\Entities\UserCompany;

class UserCompanyService
{
    private static $userChildrens = [];
    private static $userParents = [];

    public static function getUsersByParentUuid($parentUserUuid, $positionUuid, $companyUuid, $allBelow = false)
    {
        $parentUser = User::with('user_companies')
            ->where('uuid', $parentUserUuid)->first();

        if (! $parentUser) {
            return collect([]);
        }

        $position = Position::where('uuid', $positionUuid)->first();

        if (! $position) {
            return collect([]);
        }

        $modelCompany = app(config('hub.model_company'));
        $company = $modelCompany::where('uuid', $companyUuid)->first();

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

        return User::whereIn('id', self::$userChildrens)
            ->get(['uuid', 'name']);
        
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

    public static function getAllParentsByUserUuid($userUuid, $companyUuid, $onlyTop = false)
    {
        $user = User::with('user_companies')
            ->where('uuid', $userUuid)->first();

        if (! $user) {
            return collect([]);
        }

        $modelCompany = app(config('hub.model_company'));
        $company = $modelCompany::where('uuid', $companyUuid)->first();

        if (! $company) {
            return collect([]);
        }

        $userCompanyChildren = $user->user_companies
            ->where('company_id', $company->id)
            ->first()
            ->user_company_children()
            ->first();
        
        if(! $userCompanyChildren) {
            return collect([]);
        }
        
        self::getAllUserParentsByUserCompanyChildren($userCompanyChildren);

        if($onlyTop) {
            $topUserId = end(self::$userParents);
            self::$userParents = [];
            self::$userParents[] = $topUserId;
        }

        return User::whereIn('id', self::$userParents)
            ->get(['uuid', 'name']);
        
    }

    private static function getAllUserParentsByUserCompanyChildren($userCompanyChildren)
    {
        $userCompanyParent = $userCompanyChildren->user_company_parent()
                            ->first();

        self::$userParents[] = $userCompanyParent->user()->first()->id;

        $userCompanyChildren = $userCompanyParent->user_company_children()
                            ->first();
        
        if($userCompanyChildren) {
            self::getAllUserParentsByUserCompanyChildren($userCompanyChildren);
        }

    }

    public static function getAllTopParentUsersByCompanyUuid($companyUuid)
    {
        $modelCompany = app(config('hub.model_company'));
        $company = $modelCompany::where('uuid', $companyUuid)->first();

        if (! $company) {
            return collect([]);
        }

        $userCompany = UserCompany::doesntHave('user_company_children')
                        ->with('user')
                        ->where('company_id', $company->id)
                        ->get();

        $users = collect([]);

        $userCompany->each(function ($item) use ($users) {
            $users[] = [
                'uuid' => $item->user->uuid,
                'name' => $item->user->name
            ];
        });

        return $users;
    }
}
