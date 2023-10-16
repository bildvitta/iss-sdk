<?php

namespace BildVitta\Hub\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HubUserCompanyParentPosition extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hub_user_company_parent_positions';

    protected $fillable = [
        'user_company_id',
        'user_company_parent_id',
    ];

    public function user_company_children()
    {
        $userCompanyModel = app(config('hub.model_user_company'));
        return $this->belongsTo($userCompanyModel, 'user_company_id', 'id');
    }

    public function user_company_parent()
    {
        $userCompanyModel = app(config('hub.model_user_company'));
        return $this->belongsTo($userCompanyModel, 'user_company_parent_id', 'id');
    }

    public static function isParent($userCompanyId)
    {
        $userCompanyParentPositionModel = app(config('hub.model_user_company_parent_position'));
        return $userCompanyParentPositionModel::where('user_company_parent_id', $userCompanyId)->first();
    }
}
