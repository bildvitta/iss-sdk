<?php


namespace BildVitta\Hub\Http\Controllers\Users;

use BildVitta\Hub\Http\Requests\MeEditRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class MeEditController extends UsersController
{
    /**
     * @param MeEditRequest $request
     * @return JsonResponse
     */
    public function __invoke(MeEditRequest $request): JsonResponse
    {
        $redirect_uri = Config::get('hub.front_uri') . Config::get('hub.redirects.userinfo_edit');
        
        return response()->json(
            [
                'redirect' => $redirect_uri
            ]
        );
    }
}
