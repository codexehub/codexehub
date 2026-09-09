<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\Audit\AuditLogger;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:roles.view', only: ['index']),
            new Middleware('can:roles.manage', only: ['edit', 'update']),
        ];
    }

    public function index(): View
    {
        return view('admin.pages.roles.index', [
            'roles' => Role::withCount('permissions', 'users')->orderBy('id')->get(),
        ]);
    }

    public function edit(Role $role): View
    {
        abort_if($role->name === Role::SUPER_ADMIN, 403, 'The Super Admin role always has every permission.');

        return view('admin.pages.roles.edit', [
            'role' => $role,
            'groups' => PermissionCatalog::groups(),
            'assigned' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role, AuditLogger $audit): RedirectResponse
    {
        abort_if($role->name === Role::SUPER_ADMIN, 403);

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(PermissionCatalog::all())],
        ]);

        $before = $role->permissions->pluck('name')->sort()->values()->all();
        $role->syncPermissions($validated['permissions'] ?? []);
        $after = $role->permissions()->pluck('name')->sort()->values()->all();

        if ($before !== $after) {
            $audit->log('role.permissions_updated', $role, ['permissions' => $before], ['permissions' => $after]);
        }

        return redirect()->route('admin.roles.index')
            ->with('status', "Permissions updated for the {$role->label} role.");
    }
}
