<?php

namespace BildVitta\Hub\Models;

use BildVitta\Hub\Traits\UsesHubDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class UserCompany extends Model
{
    use SoftDeletes;
    use UsesHubDB;

    protected $connection = 'iss-sdk';

    protected $table = 'user_companies';

    protected $guard_name = 'web';

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    //

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function user_company_parent(): HasOne
    {
        return $this->hasOne(UserCompanyParentPosition::class, 'user_company_parent_id', 'id');
    }

    public function user_company_children(): HasOne
    {
        return $this->hasOne(UserCompanyParentPosition::class, 'user_company_id', 'id');
    }

    public function children_positions(): HasManyThrough
    {
        return $this->hasManyThrough(
            UserCompany::class,
            UserCompanyParentPosition::class,
            'user_company_parent_id',
            'id',
            'id',
            'user_company_id',
        );
    }

    public function parent_position(): HasManyThrough
    {
        return $this->hasManyThrough(
            UserCompany::class,
            UserCompanyParentPosition::class,
            'user_company_id',
            'id',
            'id',
            'user_company_parent_id',
        );
    }

    public function real_estate_developments(): MorphMany
    {
        return $this->morphMany(UserCompanyRealEstateDevelopment::class, 'linkable');
    }
}
