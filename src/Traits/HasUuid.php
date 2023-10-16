<?php

namespace BildVitta\Hub\Traits;

use Ramsey\Uuid\Uuid;

trait HasUuid
{
    public static function boot(): void
    {
        parent::boot();

        self::creating(function ($model) {
            if (collect($model->getFillable())->filter(fn (string $columnName) => $columnName === 'uuid')->isNotEmpty()) {
                $model->uuid = (string) Uuid::uuid4();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
