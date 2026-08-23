<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\TeamMemberImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller implements HasMiddleware
{
    /**
     * Team abilities are registered as Gates in AppServiceProvider. They all
     * resolve through the admin role today and will bind to real permission
     * records once the Role & Permission module lands.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:team.view', only: ['index']),
            new Middleware('can:team.create', only: ['create', 'store']),
            new Middleware('can:team.edit', only: ['edit', 'update', 'updateStatus', 'deleteImage']),
            new Middleware('can:team.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $sort = in_array($request->get('sort'), ['name', 'role', 'status', 'sort_order', 'created_at'])
            ? $request->get('sort')
            : 'sort_order';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $members = TeamMember::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->get('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->get('status')))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.team.index', compact('members', 'sort', 'direction'));
    }

    public function create(): View
    {
        $member = new TeamMember();

        return view('admin.pages.team.create', compact('member'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMember($request);

        $member = TeamMember::create($validated);

        $this->handleUploads($member, $request);

        return redirect()->route('admin.team.index')->with('status', 'Team member added successfully.');
    }

    public function edit(TeamMember $team): View
    {
        $team->load('images');

        return view('admin.pages.team.edit', ['member' => $team]);
    }

    public function update(Request $request, TeamMember $team): RedirectResponse
    {
        $validated = $this->validateMember($request, $team);

        $team->update($validated);

        $this->handleUploads($team, $request);

        return redirect()->route('admin.team.index')->with('status', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $team): RedirectResponse
    {
        if ($team->photo) {
            Storage::disk('public')->delete($team->photo);
        }

        foreach ($team->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $team->delete();

        return back()->with('status', 'Team member deleted successfully.');
    }

    public function deleteImage(TeamMemberImage $image): RedirectResponse
    {
        Gate::authorize('team.edit');

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Photo removed.');
    }

    public function updateStatus(TeamMember $team): RedirectResponse
    {
        $team->update(['status' => $team->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'Team member status updated.');
    }

    private function validateMember(Request $request, ?TeamMember $member = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:2048'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        unset($validated['photo'], $validated['gallery']);

        return $validated;
    }

    private function handleUploads(TeamMember $member, Request $request): void
    {
        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $member->update(['photo' => $request->file('photo')->store('team', 'public')]);
        }

        if ($request->hasFile('gallery')) {
            $position = (int) $member->images()->max('sort_order');

            foreach ($request->file('gallery') as $file) {
                $member->images()->create([
                    'path' => $file->store('team/gallery', 'public'),
                    'sort_order' => ++$position,
                ]);
            }
        }
    }
}
