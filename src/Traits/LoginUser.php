<?php

namespace BildVitta\Hub\Traits;

use BildVitta\Hub\Entities\HubUser;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use stdClass;
use Str;

trait LoginUser
{
    protected function loginUserByCache(string $cacheHash, string $cacheKey, string $bearerToken): Authenticatable
    {
        try {
            $hubUser = HubUser::whereToken($cacheHash)->firstOrFail();
            $userId = Cache::rememberForever(
                $cacheKey,
                fn () => $hubUser->user_id
            );
            $userModel = $this->app($this->app('config')->get('hub.model_user'));
            $user = $userModel::findOrFail($userId, ['id', 'company_id']);

            $apiUser = $this->getUser($bearerToken);
            $this->updateUserCompany($user, $apiUser);
            $this->updateUserPermissions($user, $apiUser);
        } catch (ModelNotFoundException $e) {
            $apiUser = $this->getUser($bearerToken);
            $user = $this->updateOrCreateUser($apiUser);
            $userId = $user->id;
            HubUser::firstOrCreate(
                ['token' => $cacheHash],
                ['user_id' => $userId]
            );
        }

        return $this->loginByUserId($userId);
    }

    protected function getUser(string $bearerToken): stdClass
    {
        $response = $this->app('hub', [$bearerToken])->users()->me();

        return $response->object()->result;
    }

    protected function updateOrCreateUser(stdClass $apiUser)
    {
        $userModel = $this->app('config')->get('hub.model_user');

        $user = $userModel::firstOrNew([
            'hub_uuid' => $apiUser->uuid,
            'email' => $apiUser->email,
        ], [
            'uuid' => Uuid::uuid4(),
            'name' => $apiUser->name,
            'remember_token' => Str::random(10),
            'email_verified_at' => Carbon::now(),
            'password' => bcrypt(uniqid(rand())),
        ]);

        if (property_exists($apiUser, 'is_superuser')) {
            $user->is_superuser = $apiUser->is_superuser;
        } else {
            $user->is_superuser = false;
        }

        if ($apiUser->uuid != $user->hub_uuid) {
            $user->hub_uuid = $apiUser->uuid;
        }

        $user->save();

        $this->updateUserPermissions($user, $apiUser);
        $user = $this->getUserCompany($user, $apiUser);

        return $user;
    }

    protected function updateUserCompany($user, stdClass $apiUser)
    {
        $companyModel = $this->app('config')->get('hub.model_company');

        try {
            $company = $companyModel::select('id')->where('uuid', '=', $apiUser->company)->firstOrFail();
        } catch (ModelNotFoundException $modelNotFoundException) {
            $apiCompany = $this->getCompany($apiUser->company);
            $company = $companyModel::create([
                'uuid' => $apiCompany->uuid,
                'name' => $apiCompany->name,
            ]);
        }

        $user->company_id = $company->id;

        if ($user->isDirty()) {
            $user->saveOrFail();
        }
    }

    protected function updateUserPermissions($user, stdClass $apiUser)
    {
        $userPermissions = $apiUser->user_permissions;

        if ($user->getAllPermissions()->count() !== collect($userPermissions)->flatten()->count()) {
            $this->clearPermissionsCache();
        }

        $permissionsArray = $this->userPermissionsToArray($userPermissions);

        $localPermissions = Permission::toBase()->whereIn('name', $permissionsArray)
            ->orderBy('name')->get('name')->pluck('name')->toArray();

        $permissionsDiff = array_diff($permissionsArray, $localPermissions);
        $permissionsInsert = [];

        foreach ($permissionsDiff as $permission) {
            $permissionsInsert[] = ['name' => $permission, 'guard_name' => 'web'];
        }

        if (! empty($permissionsInsert)) {
            Permission::insert($permissionsInsert);
        }

        $userLocalPermissions = $user->permissions->pluck('name')->toArray();
        $userPermissionsDiff = array_diff($permissionsArray, $userLocalPermissions);
        $userLocalPermissionsDiff = array_diff($userLocalPermissions, $permissionsArray);

        if (! empty($userPermissionsDiff) || ! empty($userLocalPermissionsDiff)) {
            $user->syncPermissions(...collect($permissionsArray)->toArray());
            $user->refresh();
        }

        return false;
    }

    private function userPermissionsToArray($userPermissions): array
    {
        $permissionsArray = [];
        foreach ($userPermissions as $key => $value) {
            if (! is_array($value)) {
                $permissionsArray[] = "$key";

                continue;
            }
            foreach ($value as $array) {
                $permissionsArray[] = "$key";
            }
        }

        return $permissionsArray;
    }

    private function clearPermissionsCache()
    {
        $permissionCacheKey = config('permission.cache.key');
        Cache::forget($permissionCacheKey);
    }

    protected function getUserCompany($user, stdClass $apiUser)
    {
        if ($apiUser->company) {
            $hubCompany = $this->getCompany($apiUser->company);
            $companyModel = $this->app('config')->get('hub.model_company');
            try {
                $company = $companyModel::where('uuid', '=', $apiUser->company)->firstOrFail();
                $company->name = $hubCompany->name;
            } catch (ModelNotFoundException $modelNotFoundException) {
                $company = new $companyModel;
                $company->uuid = $hubCompany->uuid;
                $company->name = $hubCompany->name;
            }

            $company->saveOrFail();
            $user->company_id = $company->id;
            $user->saveOrFail();
        }

        return $user;
    }

    private function getCompany(string $companyUuid): stdClass
    {
        $response = app('hub', [''])->companies()->findByUuid($companyUuid);

        return $response->object()->result;
    }

    protected function loginByUserId(int $userId): Authenticatable
    {
        return $this->app('auth')->loginUsingId($userId);
    }
}
