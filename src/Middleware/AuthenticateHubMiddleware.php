<?php

namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Exceptions\AuthenticationException;
use BildVitta\Hub\Hub;
use Closure;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;

/**
 * Class AuthAttemptMiddleware.
 *
 * @package BildVitta\Hub\Middleware
 */
class AuthenticateHubMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (is_null($token)) {
            throw new AuthenticationException(__('Bearer token é obrigatório.'));
        }

        try {
//            $response = app('hub', [$token])->users()->me();
//            $body = $response->object();

            $body = json_decode('{"status":{"code":200},"result":{"name":"B\u00e1rbara Flores Sobrinho","email":"ad-consequuntur-ullam@example.org","password":"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC\/.og\/at2.uheWG\/igi","password2":null,"uuid":"4123c474-50e4-44a7-8a00-09782516c35b","type":"cnpj","document":"83312195720480","company_name":"Alessandro Carmona Jr.","creci":"4212330","regional_creci":"GO","kind":"employee","zip_code":"85164-085","street_name":"R. Yuri","street_number":"66816","city":"Maia do Leste","state":"Amazonas","complement":"Bloco A","neighborhood":"Santa","phone":"(84) 3169-6815","is_active":1,"is_approved":true,"groups":["1ff02548-6520-4ffa-a404-5c363e45bda4"],"roles":[]}}');

            dd($body);

        } catch (RequestException $e) {
        }


        #TODO: criar user se nao existir e persistir no banco um de-para

//        auth()->loginUsingId($user->id);

        return $next($request);
    }
}