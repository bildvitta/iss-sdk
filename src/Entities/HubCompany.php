<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HubCompany extends Model
{
    use SoftDeletes;

    protected $table = 'hub_companies';

    protected $fillable = [
        'uuid',
        'name'
    ];
}
