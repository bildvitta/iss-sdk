<?php

namespace BildVitta\Hub\Services;

class UserCompanyService
{
    private static $userChildrens = [];
    private static $userParents = [];
    private static $positions = [];

    public static function getUsersByParentUuid($parentUserUuid, $positionUuid, $companyUuid, $allBelow = false)
    {
        self::$userChildrens = [];
        self::$userParents = [];
        
        $userModel = app(config('hub.model_user'));
        $parentUser = $userModel::with('user_companies')
            ->where('uuid', $parentUserUuid)->first();

        if (! $parentUser) {
            return collect([]);
        }

        $positionModel = app(config('hub.model_position'));
        $position = $positionModel::where('uuid', $positionUuid)->first();

        if (! $position) {
            return collect([]);
        }

        $companyModel = app(config('hub.model_company'));
        $company = $companyModel::where('uuid', $companyUuid)->first();

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

        return $userModel::whereIn('id', self::$userChildrens)
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

            $userCompanyParentPositionModel = app(config('hub.model_user_company_parent_position'));
            $userChildrenIsParent = $userCompanyParentPositionModel::isParent($userCompanyChildren->id);

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
        self::$userChildrens = [];
        self::$userParents = [];

        $userModel = app(config('hub.model_user'));
        $user = $userModel::with('user_companies')
            ->where('uuid', $userUuid)->first();

        if (! $user) {
            return collect([]);
        }

        $companyModel = app(config('hub.model_company'));
        $company = $companyModel::where('uuid', $companyUuid)->first();

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

        return collect(self::$userParents)->reverse();
        
    }

    private static function getAllUserParentsByUserCompanyChildren($userCompanyChildren)
    {
        $userCompanyParent = $userCompanyChildren->user_company_parent()
                            ->first();

        self::$userParents[] = $userCompanyParent;

        $userCompanyChildren = $userCompanyParent->user_company_children()
                            ->first();
        
        if($userCompanyChildren) {
            self::getAllUserParentsByUserCompanyChildren($userCompanyChildren);
        }

    }

    public static function getAllTopParentUsersByCompanyUuid($companyUuid)
    {
        $companyModel = app(config('hub.model_company'));
        $company = $companyModel::where('uuid', $companyUuid)->first();

        if (! $company) {
            return collect([]);
        }

        $userCompanyModel = app(config('hub.model_user_company'));
        $userCompany = $userCompanyModel::doesntHave('user_company_children')
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

    public static function getPositionsByOrder($companyUuid, $order = 0)
    {
        self::$positions = [];
        
        $positionModel = app(config('hub.model_position'));
        $companyModel = app(config('hub.model_company'));
        $userCompanyModel = app(config('hub.model_user_company'));

        $company = $companyModel::whereUuid($companyUuid)->first();

        if (! $company) {
            return [];
        }

        $companyId = $company->main_company_id ?? $company->id;

        $positions = $positionModel->where('company_id', $companyId)
                    ->get()
                    ->toArray();

        if(! $positions) {
            return [];
        }

        self::sortPositions($positions);

        if(! array_key_exists($order, $positions)) {
            return [];
        }

        $position = self::$positions[$order];

        return $userCompanyModel->with(['user', 'position', 'company'])
                ->where('company_id', $company->id)
                ->where('position_id', $position['id'])
                ->get()
                ->toArray();
    }

    private static function sortPositions($positions)
    {
        if(! count($positions)) {
            return;
        }
        
        foreach($positions as $key => $position) {
            if(! $position['parent_position_id']) {
                self::$positions[] = $position;
                unset($positions[$key]);
            }
        }

        foreach($positions as $key => $position) {
            $lastPosition = end(self::$positions);
            if($position['parent_position_id'] == $lastPosition['id']) {
                self::$positions[] = $position;
                unset($positions[$key]);
            }
        }

        if(count($positions)) {
            self::sortPositions($positions);
        }

    }
}
