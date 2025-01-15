<?php

namespace BildVitta\Hub\Models;

use BildVitta\Hub\Traits\UsesHubDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Position extends Model
{
    use SoftDeletes;
    use UsesHubDB;

    protected $connection = 'iss-sdk';

    protected $table = 'positions';

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent_position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'parent_position_id', 'id');
    }

    public function user_companies(): HasMany
    {
        return $this->hasMany(UserCompany::class, 'position_id', 'id');
    }
}
