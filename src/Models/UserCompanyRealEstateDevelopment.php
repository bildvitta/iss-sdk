<?php

namespace BildVitta\Hub\Models;

use BildVitta\Hub\Traits\UsesHubDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class UserCompanyRealEstateDevelopment extends Model
{
    use SoftDeletes;
    use UsesHubDB;

    protected $connection = 'iss-sdk';

    protected $table = 'user_company_real_estate_developments';

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

    public function linked(): MorphTo
    {
        return $this->morphTo();
    }
}
