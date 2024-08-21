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
        'name',
    ];

    public function main_company()
    {
        return $this->belongsTo(HubCompany::class, 'main_company_id', 'id');
    }

    public function companies()
    {
        return $this->hasMany(HubCompany::class, 'main_company_id', 'id');
    }

    public function positions()
    {
        return $this->hasMany(HubPosition::class, 'company_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(HubBrand::class, 'brand_id', 'id');
    }
}
