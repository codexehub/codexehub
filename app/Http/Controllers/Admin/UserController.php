<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->get('sort'), ['name', 'email', 'role', 'created_at']) ? $request->get('sort') : 'created_at';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->get('search');
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.users.index', compact('users', 'sort', 'direction'));
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        return view('admin.pages.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, User $user, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'editor'])],
            'roles' => ['array'],
            'roles.*' => [Rule::exists('roles', 'name')],
        ]);

        $roles = $validated['roles'] ?? [];

        if ($this->wouldRemoveLastSuperAdmin($user, $roles)) {
            return back()->withErrors(['roles' => 'You cannot remove the last Super Admin.'])->withInput();
        }

        $before = $user->roles->pluck('name')->sort()->values()->all();

        $user->update(['name' => $validated['name'], 'email' => $validated['email'], 'role' => $validated['role']]);
        $user->syncRoles($roles);

        $after = $user->roles()->pluck('name')->sort()->values()->all();
        if ($before !== $after) {
            $audit->log('user.roles_updated', $user, ['roles' => $before], ['roles' => $after]);
        }

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    /**
     * @param  list<string>  $newRoleNames
     */
    private function wouldRemoveLastSuperAdmin(User $user, array $newRoleNames): bool
    {
        if (! $user->hasRole(Role::SUPER_ADMIN) || in_array(Role::SUPER_ADMIN, $newRoleNames, true)) {
            return false;
        }

        return Role::where('name', Role::SUPER_ADMIN)->first()?->users()->count() === 1;
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return back()->with('status', 'User deleted successfully.');
    }
}
