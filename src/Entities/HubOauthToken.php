<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Model;

class HubOauthToken extends Model
{
    protected $table = 'hub_oauth_token';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_in',
        'expires_in_dt',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Check if the token is about to expire or is expired
     */
    public function is_expired(): bool
    {
        $diffDates = now()->diff($this->expires_in_dt);
        if ($diffDates->days <= 1) {
            return true;
        }

        return false;
    }
}
