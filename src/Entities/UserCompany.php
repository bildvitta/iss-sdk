<?php

namespace BildVitta\Hub\Entities;

use App\Services\UserCompanyService;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\UserCompany
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $company_id
 * @property int|null $position_id
 * @property bool $is_seller
 * @property bool $has_all_real_estate_developments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $has_specific_permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserCompany> $children_positions
 * @property-read int|null $children_positions_count
 * @property-read \App\Models\Company $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserCompany> $parent_positions
 * @property-read int|null $parent_positions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Position|null $position
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserCompanyRealEstateDevelopments> $real_estate_developments
 * @property-read int|null $real_estate_developments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserCompanyParentPosition> $user_company_children
 * @property-read int|null $user_company_children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserCompanyParentPosition> $user_company_parent
 * @property-read int|null $user_company_parent_count
 *
 * @method static \Database\Factories\UserCompanyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereHasAllRealEstateDevelopments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereHasSpecificPermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereIsSeller($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany withoutTrashed()
 *
 * @mixin \Eloquent
 */
class UserCompany extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasRoles;

    protected $table = 'user_companies';

    protected $fillable = [
        'uuid',
        'user_id',
        'company_id',
        'position_id',
        'is_seller',
        'has_all_real_estate_developments',
        'has_specific_permissions',
    ];

    protected $casts = [
        'is_seller' => 'boolean',
        'has_all_real_estate_developments' => 'boolean',
        'has_specific_permissions' => 'boolean',
    ];

    protected $guard_name = 'api';

    public function companyName(): Attribute
    {
        return Attribute::get(function () {
            return $this->company->name;
        });
    }

    public function user(): BelongsTo
    {
        $userModel = app(config('hub.model_user'));
        return $this->belongsTo($userModel, 'user_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function user_company_parent()
    {
        return $this->hasMany(UserCompanyParentPosition::class, 'user_company_parent_id', 'id');
    }

    public function user_company_children()
    {
        return $this->hasMany(UserCompanyParentPosition::class, 'user_company_id', 'id');
    }

    public function children_positions()
    {
        return $this->hasManyThrough(
            UserCompany::class,
            UserCompanyParentPosition::class,
            'user_company_parent_id',
            'id',
            'id',
            'user_company_id',
        );
    }

    public function parent_positions()
    {
        return $this->hasManyThrough(
            UserCompany::class,
            UserCompanyParentPosition::class,
            'user_company_id',
            'id',
            'id',
            'user_company_parent_id',
        );
    }

    public function real_estate_developments()
    {
        return $this->hasMany(UserCompanyRealEstateDevelopments::class, 'user_company_id', 'id');
    }
}
