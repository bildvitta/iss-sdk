<?php

namespace BildVitta\Hub\Models\Approvers;

use BildVitta\Hub\Enums\Approvers\Type;
use BildVitta\Hub\Models\Company;
use BildVitta\Hub\Models\User;
use BildVitta\Hub\Traits\UsesHubDB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Approver extends Model
{
    use SoftDeletes;
    use UsesHubDB;

    protected $connection = 'iss-sdk';

    protected $table = 'approvers';

    protected $fillable = [
        'company_id',
        'user_id',
        'type',
    ];

    protected $casts = [
        'type' => Type::class,
    ];

    public static function boot(): void
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo|Builder
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function company(): BelongsTo|Builder
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function verges(): BelongsToMany
    {
        return $this->belongsToMany(Verge::class, 'approver_verge', 'approver_id', 'verge_id');
    }
}