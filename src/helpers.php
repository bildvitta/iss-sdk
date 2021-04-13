<?php

namespace BildVitta\Hub;

use App\Models\User;
use BildVitta\Hub\Exceptions\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

if (! function_exists('user')) {
    /**
     * @param  array  $with
     *
     * @return User
     *
     * @throws ModelNotFoundException
     * @throws AuthenticationException
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    function user(array $with = []): User
    {
        /** @var User $user */
        $user = auth()->user();

//        dd($user);

        if (! is_null($user)) {
            return User::with($with)->findOrFail($user->id);
        }

        throw new AuthenticationException('user not authenticated.');
    }
}
