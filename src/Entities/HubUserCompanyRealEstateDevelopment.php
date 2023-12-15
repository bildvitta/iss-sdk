<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubUserCompanyRealEstateDevelopment extends Model
{
    use HasFactory;

    protected $table = 'hub_user_company_real_estate_developments';

    protected $fillable = [
        'user_company_id',
        'real_estate_development_uuid',
    ];

    public function user_company()
    {
        $userCompanyModel = app(config('hub.model_user_company'));

        return $this->belongsTo($userCompanyModel, 'user_company_id', 'id');
    }
}
