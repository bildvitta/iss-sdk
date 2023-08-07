<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\UserCompanyParentPosition
 *
 * @property int $id
 * @property int $user_company_id
 * @property int $user_company_parent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\UserCompany $user_company_children
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition whereUserCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyParentPosition whereUserCompanyParentId($value)
 *
 * @mixin \Eloquent
 */
class UserCompanyParentPosition extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_company_id',
        'user_company_parent_id',
    ];

    public function user_company_children()
    {
        return $this->belongsTo(UserCompany::class, 'user_company_id', 'id');
    }

    public function user_company_parent()
    {
        return $this->belongsTo(UserCompany::class, 'user_company_parent_id', 'id');
    }

    public static function isParent($userCompanyId)
    {
        return self::where('user_company_parent_id', $userCompanyId)->first();
    }
}
