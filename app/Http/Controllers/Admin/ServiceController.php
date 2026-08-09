<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        $service = Service::create($validated);

        $this->syncFeatures($service, $request);
        $this->handleUpload($service, $request);

        return redirect()->route('admin.services.index')->with('status', 'Service created successfully.');
    }

    public function edit(Service $service): View
    {
        $service->load('features');

        return view('admin.pages.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $this->validateService($request, $service);

        $service->update($validated);

        $this->syncFeatures($service, $request);
        $this->handleUpload($service, $request);

        return redirect()->route('admin.services.index')->with('status', 'Service updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

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
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'max:2048'],
            'features_icon' => ['nullable', 'array'],
            'features_icon.*' => ['nullable', 'string', 'max:100'],
            'features_text' => ['nullable', 'array'],
            'features_text.*' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['featured'] = $request->boolean('featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['slug'] = $validated['slug'] ? Str::slug($validated['slug']) : null;

        unset($validated['image'], $validated['features_icon'], $validated['features_text']);

        return $validated;
    }

    private function syncFeatures(Service $service, Request $request): void
    {
        $icons = $request->input('features_icon', []);
        $texts = $request->input('features_text', []);

        $service->features()->delete();

        foreach ($texts as $index => $text) {
            $text = trim((string) $text);

            if ($text === '') {
                continue;
            }

            $service->features()->create([
                'icon' => trim((string) ($icons[$index] ?? '')) ?: 'bi-check-circle',
                'text' => $text,
                'sort_order' => $index,
            ]);
        }
    }

    private function handleUpload(Service $service, Request $request): void
    {
        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $service->update(['image' => $request->file('image')->store('services', 'public')]);
        }
    }
}
