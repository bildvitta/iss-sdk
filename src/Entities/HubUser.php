<?php
/** @noinspection ALL */

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HubUser.
 *
 * @package BildVitta\Hub\Entities
 */
class HubUser extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    protected $primaryKey = 'hub_uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['token', 'user_id'];
}