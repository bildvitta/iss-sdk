<?php

namespace BildVitta\Hub\Models;

use BildVitta\Hub\Traits\UsesHubDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class User extends Model
{
    use SoftDeletes;
    use UsesHubDB;

    protected $connection = 'iss-sdk';

    protected $table = 'users';

    protected $guard_name = 'web';

    public const TYPE_LIST = [
        'cpf' => 'Pessoa física',
        'cnpj' => 'Pessoa jurídica',
    ];

    public const KIND_LIST = [
        'self_employed' => 'Autônomo',
        'employee' => 'Colaborador',
    ];

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
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function user_companies(): HasMany
    {
        return $this->hasMany(UserCompany::class, 'user_id', 'id');
    }
}
