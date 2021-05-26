<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class HubUser.
 *
 * @package BildVitta\Hub\Entities
 */
class HubUser extends Model
{
    use SoftDeletes;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    protected $primaryKey = null;

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
