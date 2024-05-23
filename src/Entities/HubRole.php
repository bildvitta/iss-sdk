<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HubRole extends \Spatie\Permission\Models\Role
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'hub_company_id',
        'has_all_real_estate_developments',
        'is_post_construction',
    ];

    protected $casts = [
        'has_all_real_estate_developments' => 'boolean',
        'is_post_construction' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(HubCompany::class, 'hub_company_id', 'id');
    }

    public function positions(): BelongsToMany
    {
        return $this->morphedByMany(
            HubPosition::class,
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
        );
    }
}
