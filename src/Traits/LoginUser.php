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
            $user = $userModel::findOrFail($userId, ['id']);

            $apiUser = $this->getUser($bearerToken);
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

    /**
     * @param string $bearerToken
     * @return stdClass
     */
    protected function getUser(string $bearerToken): stdClass
    {
        $response = $this->app('hub', [$bearerToken])->users()->me();
        return $response->object()->result;
    }

    protected function updateOrCreateUser(stdClass $apiUser)
    {
        $userModel = $this->app('config')->get('hub.model_user');

        $is_superuser = false;
        if (property_exists($apiUser, 'is_superuser')) {
            $is_superuser = $apiUser->is_superuser;
        }

        $user = $userModel::updateOrCreate([
            'hub_uuid' => $apiUser->uuid,
            'email' => $apiUser->email
        ], [
            'uuid' => Uuid::uuid4(),
            'name' => $apiUser->name,
            'remember_token' => Str::random(10),
            'email_verified_at' => Carbon::now(),
            'password' => bcrypt(uniqid(rand()))
        ]);

        if ($is_superuser) {
            $user->is_superuser = $is_superuser;
            $user->save();
        }

        if ($apiUser->uuid != $user->hub_uuid) {
            $user->hub_uuid = $apiUser->uuid;
            $user->save();
        }

        $this->updateUserPermissions($user, $apiUser);
        $user = $this->getUserCompany($user, $apiUser);

        return $user;
    }

    protected function updateUserPermissions($user, stdClass $apiUser)
    {
        $permissions = $apiUser->user_permissions;

        if ($user->getAllPermissions()->count() !== collect($permissions)->flatten()->count()) {
            $this->clearPermissionsCache();
        }

        $userPermissions = [];
        foreach ($permissions as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $array) {
                    $userPermissions[] = Permission::findOrCreate("$key.$array", 'web');
                }
            } else {
                $userPermissions[] = Permission::findOrCreate("$key.$value", 'web');
            }
        }

        $user->syncPermissions(... collect($userPermissions)->pluck('name')->toArray());

        return false;
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
                $company = new $companyModel();
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
