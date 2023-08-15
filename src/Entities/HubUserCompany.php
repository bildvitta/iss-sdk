<?php

namespace BildVitta\Hub\Entities;

use BildVitta\Hub\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class HubUserCompany extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasRoles;

    protected $table = 'hub_user_companies';

    protected $fillable = [
        'uuid',
        'user_id',
        'company_id',
        'position_id',
        'is_seller',
        'has_all_real_estate_developments',
        'has_specific_permissions',
    ];

    protected $casts = [
        'is_seller' => 'boolean',
        'has_all_real_estate_developments' => 'boolean',
        'has_specific_permissions' => 'boolean',
    ];

    public function companyName(): Attribute
    {
        return Attribute::get(function () {
            return $this->company->name;
        });
    }

    public function user(): BelongsTo
    {
        $userModel = app(config('hub.model_user'));
        return $this->belongsTo($userModel, 'user_id', 'id');
    }

    public function company(): BelongsTo
    {
        $companyModel = app(config('hub.model_company'));
        return $this->belongsTo($companyModel, 'company_id', 'id');
    }

    public function position(): BelongsTo
    {
        $positionModel = app(config('hub.model_position'));
        return $this->belongsTo($positionModel, 'position_id', 'id');
    }

    public function user_company_parent()
    {
        $userCompanyParentPositionModel = app(config('hub.model_user_company_parent_position'));
        return $this->hasMany($userCompanyParentPositionModel, 'user_company_parent_id', 'id');
    }

    public function user_company_children()
    {
        $userCompanyParentPositionModel = app(config('hub.model_user_company_parent_position'));
        return $this->hasMany($userCompanyParentPositionModel, 'user_company_id', 'id');
    }

    public function children_positions()
    {
        $userCompanyModel = app(config('hub.model_user_company'));
        $userCompanyParentPositionModel = app(config('hub.model_user_company_parent_position'));
        return $this->hasManyThrough(
            $userCompanyModel,
            $userCompanyParentPositionModel,
            'user_company_parent_id',
            'id',
            'id',
            'user_company_id',
        );
    }

    public function parent_positions()
    {
        $userCompanyModel = app(config('hub.model_user_company'));
        $userCompanyParentPositionModel = app(config('hub.model_user_company_parent_position'));
        return $this->hasManyThrough(
            $userCompanyModel,
            $userCompanyParentPositionModel,
            'user_company_id',
            'id',
            'id',
            'user_company_parent_id',
        );
    }

    public function real_estate_developments()
    {
        $userCompanyRealEstateDevelopmentsModel = app(config('hub.model_user_company_real_estate_developments'));
        return $this->hasMany($userCompanyRealEstateDevelopmentsModel, 'user_company_id', 'id');
    }
}
