<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Model;

class HubCompany extends Model
{
    protected $table = 'hub_companies';

    protected $fillable = [
        'uuid',
        'name'
    ];
}
