<?php

namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Entities\HubUser;
use BildVitta\Hub\Http\Requests\LogoutRequest;
use Illuminate\Support\Facades\Cache;

/**
 * Class LogoutController
 */
class LogoutController extends AuthController
{
    public function __invoke(LogoutRequest $request)
    {
        $hubuserModel = HubUser::where('user_id', '=', $request->user()?->id);

        $cacheKeys = $hubuserModel->get()->map(fn (HubUser $hubUser) => $hubUser->token.'-check')
            ->filter()
            ->toArray();

        Cache::deleteMultiple($cacheKeys);

        $jsonResponse = [
            'logout_url' => config('hub.front_uri').'/auth/logout',
        ];
        HubUser::where('user_id', '=', $request->user()->id)->delete();

        $hubuserModel->delete();

        return response()->json($jsonResponse);
    }
}
