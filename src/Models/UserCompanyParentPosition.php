<?php

namespace BildVitta\Hub\Models;

use BildVitta\Hub\Traits\UsesHubDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class UserCompanyParentPosition extends Model
{
    use SoftDeletes;
    use UsesHubDB;

    protected $connection = 'iss-sdk';

    protected $table = 'user_company_parent_positions';

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

    public function user_company_children(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'user_company_id', 'id');
    }

    public function user_company_parent(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'user_company_parent_id', 'id');
    }
}
