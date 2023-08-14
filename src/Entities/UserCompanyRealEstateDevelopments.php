<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCompanyRealEstateDevelopments extends Model
{
    use HasFactory;

    protected $table = 'hub_user_company_real_estate_developments';

    protected $fillable = [
        'user_company_id',
        'real_estate_development_uuid',
    ];

    public function user_company()
    {
        return $this->belongsTo(UserCompany::class, 'user_company_id', 'id');
    }
}
