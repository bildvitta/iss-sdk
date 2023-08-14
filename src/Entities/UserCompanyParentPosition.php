<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCompanyParentPosition extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hub_user_parent_positions';

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
