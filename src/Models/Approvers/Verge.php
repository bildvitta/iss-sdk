<?php

namespace BildVitta\Hub\Models\Approvers;

use BildVitta\Hub\Enums\Approvers\Type;
use BildVitta\Hub\Traits\UsesHubDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Verge extends Model
{
    use SoftDeletes;
    use UsesHubDB;

    protected $connection = 'iss-sdk';

    protected $table = 'verges';

    protected $fillable = [
        'type',
        'name',
        'number',
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

    public function approvers(): BelongsToMany
    {
        return $this->belongsToMany(Approver::class, 'approver_verge', 'verge_id', 'approver_id');
    }
}