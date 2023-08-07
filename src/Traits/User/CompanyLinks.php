<?php


namespace BildVitta\Hub\Traits\User;

use BildVitta\Hub\Entities\UserCompany;
use App\Models\Company;

trait CompanyLinks
{
    public function user_companies()
    {
        return $this->hasMany(UserCompany::class, 'user_id', 'id');
    }
    
    public function linked_company()
    {
        return $this->company->linked_company();
    }

    public function company_links()
    {
        $userCompany = app(config('hub.model_company'));
        return $this->hasManyThrough(
            $userCompany,
            UserCompany::class,
            'user_id',
            'id',
            'id',
            'company_id'
        );
    }

    public function company_link()
    {
        return $this->company();
    }
}
