<?php

namespace BildVitta\Hub\Traits\User;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

trait HasCompanyLinks
{
    use HasRoles;

    public function user_company(): HasOne
    {
        $userCompanyModel = app(config('hub.model_user_company'));

        $user_company = $this->hasOne($userCompanyModel, 'user_id', 'id')
            ->where('company_id', $this->company_id);

        if (! $user_company->exists() && $this->hasMainLinkedCompanies()) {
            $main_companies = $this->getMainLinkedCompanies();
            $user_company = $this->hasOne($userCompanyModel, 'user_id', 'id')
                ->whereIn('company_id', $main_companies);
        }

        return $user_company;
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
            return $this->user_company?->real_estate_developments ?? collect([]);
        });
    }

    public function hasAllRealEstateDevelopments(): Attribute
    {
        return Attribute::get(function () {
            return $this->user_company?->has_all_real_estate_developments ?? false;
        });
    }

    public function getMainLinkedCompanies(): array
    {
        $hubCompanyModel = app(config('hub.model_company'));
        $tableName = $hubCompanyModel->getTable();

        return $this->company_links()->whereNull('main_company_id')->get(["$tableName.id"])->pluck(['id'])->toArray();
    }

    public function hasMainLinkedCompanies(): bool
    {
        return $this->company_links()->whereNull('main_company_id')->exists();
    }
}
