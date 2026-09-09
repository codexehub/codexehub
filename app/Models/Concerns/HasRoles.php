<?php

namespace App\Models\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Role / permission resolution for the User model.
 *
 * `super-admin` is not special-cased here — the seeder grants it every
 * permission, and AppServiceProvider::boot() adds a Gate::before() blanket pass
 * so it survives a permission being added before the seeder is re-run.
 */
trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @param  string|list<string>  $role  one or more role machine keys
     */
    public function hasRole(string|array $role): bool
    {
        return $this->loadMissing('roles')->roles
            ->pluck('name')
            ->intersect((array) $role)
            ->isNotEmpty();
    }

    public function hasPermissionTo(string $permission): bool
    {
        return $this->permissionNames()->contains($permission);
    }

    /**
     * Distinct permission names granted through any assigned role (memoised via
     * the eager-loaded relations).
     */
    public function permissionNames(): Collection
    {
        return $this->loadMissing('roles.permissions')->roles
            ->flatMap->permissions
            ->pluck('name')
            ->unique()
            ->values();
    }

    public function assignRole(string ...$names): void
    {
        $this->roles()->syncWithoutDetaching(
            Role::whereIn('name', $names)->pluck('id')
        );
        $this->unsetRelation('roles');
    }

    /**
     * @param  list<string>  $names
     */
    public function syncRoles(array $names): void
    {
        $this->roles()->sync(
            Role::whereIn('name', $names)->pluck('id')
        );
        $this->unsetRelation('roles');
    }
}
