<?php


namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Http\Requests\RefreshRequest;

/**
 * Class RefreshController
 * @package BildVitta\Hub\Http\Controllers\Auth
 */
class RefreshController extends AuthController
{
    public function __invoke(RefreshRequest $request)
    {
        return response()->json();
    }
}
