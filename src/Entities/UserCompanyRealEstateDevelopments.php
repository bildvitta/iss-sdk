<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserCompanyRealEstateDevelopments
 *
 * @property int $id
 * @property int $user_company_id
 * @property string $real_estate_development_uuid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyRealEstateDevelopments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyRealEstateDevelopments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyRealEstateDevelopments query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyRealEstateDevelopments whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyRealEstateDevelopments whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyRealEstateDevelopments whereRealEstateDevelopmentUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyRealEstateDevelopments whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompanyRealEstateDevelopments whereUserCompanyId($value)
 *
 * @mixin \Eloquent
 */
class UserCompanyRealEstateDevelopments extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_company_id',
        'real_estate_development_uuid',
    ];
}
