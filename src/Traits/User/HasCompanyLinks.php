<?php


namespace BildVitta\Hub\Traits\User;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;

trait HasCompanyLinks
{
    use HasRoles;

    public function user_company(): HasOne
    {
        $userCompanyModel = app(config('hub.model_user_company'));
        return $this->hasOne($userCompanyModel, 'user_id', 'id')
            ->where('company_id', $this->company_id);
    }

    public function user_companies(): HasMany
    {
        $userCompanyModel = app(config('hub.model_user_company'));
        return $this->hasMany($userCompanyModel, 'user_id', 'id');
    }

    public function linked_company()
    {
        return $this->company->linked_company();
    }

    public function company_links(): HasManyThrough
    {
        $userCompany = app(config('hub.model_company'));
        $userCompanyModel = app(config('hub.model_user_company'));
        return $this->hasManyThrough(
            $userCompany,
            $userCompanyModel,
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

    public function getAllPermissions(): Collection
    {
        return $this->user_company?->getAllPermissions() ?? collect([]);
    }

    public function realEstateDevelopments(): Attribute
    {
        return Attribute::get(function () {
            return $this->user_company?->realEstateDevelopments ?? collect([]);
        });
    }
}
