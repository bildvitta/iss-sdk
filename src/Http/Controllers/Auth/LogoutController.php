<?php


namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Http\Requests\LogoutRequest;

/**
 * Class LogoutController
 * @package BildVitta\Hub\Http\Controllers\Auth
 */
class LogoutController extends AuthController
{
    public function __invoke(LogoutRequest $request)
    {
        return response()->json();
    }
}
