<?php

namespace BildVitta\Hub\Entities;

use BildVitta\Hub\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class HubPosition extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasRoles;

    protected $table = 'hub_positions';

    protected $fillable = [
        'uuid',
        'name',
        'parent_position_id',
        'company_id',
    ];

    public function parent_position(): BelongsTo
    {
        $positionModel = app(config('hub.model_position'));
        return $this->belongsTo($positionModel, 'parent_position_id', 'id');
    }

    public function user_companies()
    {
        $userCompanyModel = app(config('hub.model_user_company'));
        return $this->hasMany($userCompanyModel, 'position_id', 'id');
    }
}
