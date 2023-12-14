<?php

namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Entities\HubUser;
use BildVitta\Hub\Http\Requests\LogoutRequest;

/**
 * Class LogoutController
 */
class LogoutController extends AuthController
{
    public function __invoke(LogoutRequest $request)
    {
        $jsonResponse = [
            'logout_url' => config('hub.front_uri').'/auth/logout',
        ];
        HubUser::where('user_id', '=', $request->user()->id)->delete();

        return response()->json($jsonResponse);
    }
}
