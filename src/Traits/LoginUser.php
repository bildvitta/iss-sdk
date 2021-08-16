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
            $userModel::findOrFail($userId, ['id']);
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
        foreach ($permissions as $key => $value) {
            $findPermission = Permission::findOrCreate("$key.$value", 'web');
        }
        if (!empty($permissions)) {
            $user->givePermissionTo(... collect($permissions)->pluck('name')->toArray());
            return true;
        }
        return false;
    }

    protected function loginByUserId(int $userId): Authenticatable
    {
        return $this->app('auth')->loginUsingId($userId);
    }

    protected function getUserCompany($user, stdClass $apiUser)
    {
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

        return $user;
    }

    private function getCompany(string $companyUuid): stdClass
    {
        $response = app('hub', [''])->companies()->findByUuid($companyUuid);
        return $response->object()->result;
    }
}
