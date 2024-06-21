<?php

namespace App\Scopes\Companies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PublicListScope implements Scope
{
    public function __construct(
        protected bool $public_list
    ) {}

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereIn('public_list', $this->public_list);
    }
}
