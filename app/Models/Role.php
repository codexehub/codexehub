<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use Auditable;

    public const SUPER_ADMIN = 'super-admin';

    protected $fillable = ['name', 'label', 'description'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains('name', $permission);
    }

    /**
     * Replace this role's permissions with the given permission names.
     *
     * @param  list<string>  $names
     */
    public function syncPermissions(array $names): void
    {
        $this->permissions()->sync(
            Permission::whereIn('name', $names)->pluck('id')
        );
        $this->unsetRelation('permissions');
    }
}
