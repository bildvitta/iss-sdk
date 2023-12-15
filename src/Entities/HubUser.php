<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class HubUser.
 */
class HubUser extends Model
{
    use SoftDeletes;

    public $incrementing = false;

    protected $table = 'hub_users';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    protected $primaryKey = 'token';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'token',
        'user_id',
    ];
}
