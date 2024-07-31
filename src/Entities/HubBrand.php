<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HubBrand extends Model
{
    use SoftDeletes;

    protected $table = 'hub_brands';

    protected $fillable = [
        'uuid',
        'name',
    ];

    public function companies()
    {
        return $this->belongsTo(HubCompany::class, 'brand_id', 'id');
    }
}
