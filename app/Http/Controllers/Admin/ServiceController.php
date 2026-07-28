<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->get('sort'), ['title', 'featured', 'status', 'sort_order', 'created_at'])
            ? $request->get('sort')
            : 'sort_order';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $services = Service::query()
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->get('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->get('status')))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.services.index', compact('services', 'sort', 'direction'));
    }

    public function create(): View
    {
        $service = new Service();

        return view('admin.pages.services.create', compact('service'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateService($request);

        Service::create($validated);

        return redirect()->route('admin.services.index')->with('status', 'Service created successfully.');
    }

    public function edit(Service $service): View
    {
        return view('admin.pages.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $this->validateService($request, $service);

        $service->update($validated);

        return redirect()->route('admin.services.index')->with('status', 'Service updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return back()->with('status', 'Service deleted successfully.');
    }

    public function updateStatus(Service $service): RedirectResponse
    {
        $service->update(['status' => $service->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'Service status updated.');
    }

    public function toggleFeatured(Service $service): RedirectResponse
    {
        $service->update(['featured' => ! $service->featured]);

        return back()->with('status', 'Homepage visibility updated.');
    }

    private function validateService(Request $request, ?Service $service = null): array
    {
        $validated = $request->validate([
            'icon' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($service?->id)],
            'short_description' => ['required', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['featured'] = $request->boolean('featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['slug'] = $validated['slug'] ? Str::slug($validated['slug']) : null;

        return $validated;
    }
}
