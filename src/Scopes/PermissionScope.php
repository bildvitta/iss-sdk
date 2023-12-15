<?php

namespace BildVitta\Hub\Scopes;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 *  It scopes a model on top of the permission passed to it.
 */
class PermissionScope implements Scope
{
    protected string $permission;

    protected string $attribute;

    protected ?Authenticatable $user;

    public function __construct(string $permission, string $attribute = 'uuid')
    {
        $this->permission = $permission;
        $this->attribute = $attribute;
        $this->user = Auth::user();
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Builder $builder, Model $model)
    {
        if (! $this->user || $this->user->is_superuser) {
            return;
        }

        $permissions = $this->getPermissionsIds();

        if ($permissions->count()) {
            $builder->whereIn($this->attribute, $permissions);
        }
    }

    /**
     * @return Collection
     */
    protected function getPermissionsIds()
    {
        $permission = $this->permission;

        return $this->user->getAllPermissions()
            ->pluck('name')
            ->filter(function ($value) use ($permission) {
                if (substr($value, 0, strlen($permission)) === $permission) {
                    $substr = substr($value, strlen($permission));
                    $substr = ltrim($substr, '.');
                    if (in_array($substr, ['*', 'template'])) {
                        return false;
                    }

                    return true;
                }

                return false;
            })
            ->map(function ($value) use ($permission) {
                $substr = substr($value, strlen($permission));

                return ltrim($substr, '.');
            });
    }
}
