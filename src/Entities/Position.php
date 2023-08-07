<?php

namespace BildVitta\Hub\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

/**
 * App\Models\Position
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_position_id
 * @property string $uuid
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Position|null $parent_position
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserCompany> $user_companies
 * @property-read int|null $user_companies_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Position newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Position newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Position onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Position query()
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereParentPositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Position withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Position extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    protected $fillable = [
        'uuid',
        'name',
        'parent_position_id',
        'company_id',
    ];

    public function roles()
    {
        return $this->hasMany(Role::class, 'position_id', 'id');
    }

    public function parent_position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'parent_position_id', 'id');
    }

    public function user_companies()
    {
        return $this->hasMany(UserCompany::class, 'position_id', 'id');
    }
}
