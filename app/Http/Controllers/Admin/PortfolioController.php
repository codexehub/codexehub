<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\PortfolioImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->get('sort'), ['title', 'client_name', 'featured', 'status', 'sort_order', 'created_at'])
            ? $request->get('sort')
            : 'sort_order';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $portfolios = Portfolio::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->get('search');
                $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->get('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->get('status')))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $categories = PortfolioCategory::query()->orderBy('name')->get();

        return view('admin.pages.portfolio.index', compact('portfolios', 'categories', 'sort', 'direction'));
    }

    public function create(): View
    {
        $categories = PortfolioCategory::active()->orderBy('name')->get();
        $portfolio = new Portfolio();

        return view('admin.pages.portfolio.create', compact('categories', 'portfolio'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePortfolio($request);

        $portfolio = Portfolio::create($validated);

        $this->syncTechnologies($portfolio, $request->get('technologies'));
        $this->handleUploads($portfolio, $request);

        return redirect()->route('admin.portfolio.index')->with('status', 'Project created successfully.');
    }

    public function edit(Portfolio $portfolio): View
    {
        $categories = PortfolioCategory::active()->orderBy('name')->get();
        $portfolio->load('images', 'technologies');

        return view('admin.pages.portfolio.edit', compact('portfolio', 'categories'));
    }

    public function update(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $validated = $this->validatePortfolio($request, $portfolio);

        $portfolio->update($validated);

        $this->syncTechnologies($portfolio, $request->get('technologies'));
        $this->handleUploads($portfolio, $request);

        return redirect()->route('admin.portfolio.index')->with('status', 'Project updated successfully.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        foreach ($portfolio->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $portfolio->delete();

        return back()->with('status', 'Project deleted successfully.');
    }

    public function duplicate(Portfolio $portfolio): RedirectResponse
    {
        $copy = $portfolio->replicate(['slug']);
        $copy->title = "{$portfolio->title} (Copy)";
        $copy->status = 'inactive';
        $copy->featured = false;
        $copy->save();

        foreach ($portfolio->technologies as $technology) {
            $copy->technologies()->create(['technology_name' => $technology->technology_name]);
        }

        return back()->with('status', 'Project duplicated successfully.');
    }

    public function updateStatus(Portfolio $portfolio): RedirectResponse
    {
        $portfolio->update(['status' => $portfolio->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'Project status updated.');
    }

    public function toggleFeatured(Portfolio $portfolio): RedirectResponse
    {
        $portfolio->update(['featured' => ! $portfolio->featured]);

        return back()->with('status', 'Homepage visibility updated.');
    }

    public function deleteImage(PortfolioImage $image): RedirectResponse
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Image removed.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:portfolios,id'],
            'action' => ['required', Rule::in(['delete', 'activate', 'deactivate'])],
        ]);

        $portfolios = Portfolio::query()->whereIn('id', $validated['ids'])->get();

        foreach ($portfolios as $portfolio) {
            match ($validated['action']) {
                'delete' => tap($portfolio, function (Portfolio $p) {
                    foreach ($p->images as $image) {
                        Storage::disk('public')->delete($image->path);
                    }
                })->delete(),
                'activate' => $portfolio->update(['status' => 'active']),
                'deactivate' => $portfolio->update(['status' => 'inactive']),
            };
        }

        return back()->with('status', 'Bulk action applied successfully.');
    }

    private function validatePortfolio(Request $request, ?Portfolio $portfolio = null): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:portfolio_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('portfolios', 'slug')->ignore($portfolio?->id)],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:2048'],
            'gallery.*' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['featured'] = $request->boolean('featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['slug'] = $validated['slug'] ? Str::slug($validated['slug']) : null;

        unset($validated['thumbnail'], $validated['banner'], $validated['gallery']);

        return $validated;
    }

    private function syncTechnologies(Portfolio $portfolio, ?string $csv): void
    {
        $portfolio->technologies()->delete();

        $names = collect(explode(',', (string) $csv))
            ->map(fn ($name) => trim($name))
            ->filter();

        foreach ($names as $name) {
            $portfolio->technologies()->create(['technology_name' => $name]);
        }
    }

    private function handleUploads(Portfolio $portfolio, Request $request): void
    {
        if ($request->hasFile('thumbnail')) {
            $portfolio->images()->where('type', 'thumbnail')->get()->each(function (PortfolioImage $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
            $this->storeImage($portfolio, $request->file('thumbnail'), 'thumbnail');
        }

        if ($request->hasFile('banner')) {
            $portfolio->images()->where('type', 'banner')->get()->each(function (PortfolioImage $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
            $this->storeImage($portfolio, $request->file('banner'), 'banner');
        }

        if ($request->hasFile('gallery')) {
            $nextOrder = (int) $portfolio->images()->where('type', 'gallery')->max('sort_order') + 1;

            foreach ($request->file('gallery') as $file) {
                $this->storeImage($portfolio, $file, 'gallery', $nextOrder);
                $nextOrder++;
            }
        }
    }

    private function storeImage(Portfolio $portfolio, UploadedFile $file, string $type, int $sortOrder = 0): void
    {
        $path = $file->store('portfolio', 'public');

        $portfolio->images()->create([
            'path' => $path,
            'type' => $type,
            'sort_order' => $sortOrder,
        ]);
    }
}
