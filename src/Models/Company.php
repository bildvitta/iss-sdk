<?php

namespace BildVitta\Hub\Models;

use BildVitta\Hub\Traits\UsesHubDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Company extends Model
{
    use SoftDeletes;
    use UsesHubDB;

    protected $connection = 'iss-sdk';

    protected $table = 'companies';

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

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            UserCompany::class,
            'company_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function main_company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'main_company_id', 'id');
    }

    public function sub_companies(): HasMany
    {
        return $this->hasMany(Company::class, 'main_company_id', 'id');
    }

    public function user_companies(): HasMany
    {
        return $this->hasMany(UserCompany::class, 'company_id', 'id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
