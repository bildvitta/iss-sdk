<?php


namespace BildVitta\Hub\Http\Controllers\Users;

use BildVitta\Hub\Entities\HubUser;
use BildVitta\Hub\Http\Requests\MeRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class ClearCacheController extends UsersController
{
    /**
     * @param MeRequest $request
     * @return Response
     */
    public function __invoke(MeRequest $request, $user): Response
    {
        $user = config('hub.model_user')::where('hub_uuid', $user);

        // clearing user cache
        return Cache::forget('hub.me.' . $user->hub_uuid);
    }
}
