<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller implements HasMiddleware
{
    /**
     * The marketing `products` table is also the SRS §52 control-plane product
     * (PROD-001), so this screen is gated by the control-plane `products.*`
     * permissions (PROD-002) — `products.view` to read, `products.manage` for
     * every write. Package Manager holds both (SRS §105).
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:products.view', only: ['index']),
            new Middleware('can:products.manage', only: ['create', 'store', 'edit', 'update', 'updateStatus', 'toggleFeatured', 'deleteImage', 'destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $sort = in_array($request->get('sort'), ['title', 'type', 'featured', 'status', 'sort_order', 'created_at'])
            ? $request->get('sort')
            : 'sort_order';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->get('search').'%'))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->get('type')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->get('status')))
            ->when($request->filled('category'), fn ($query) => $query->where('product_category_id', $request->get('category')))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $categories = ProductCategory::query()->orderBy('sort_order')->get();

        return view('admin.pages.products.index', compact('products', 'categories', 'sort', 'direction'));
    }

    public function create(): View
    {
        $product = new Product;
        $categories = ProductCategory::query()->orderBy('sort_order')->get();

        return view('admin.pages.products.create', compact('product', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $product = Product::create($validated);

        $this->syncFeatures($product, $request);
        $this->handleUploads($product, $request);

        return redirect()->route('admin.products.index')->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load(['features', 'images']);
        $categories = ProductCategory::query()->orderBy('sort_order')->get();

        return view('admin.pages.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);

        $product->update($validated);

        $this->syncFeatures($product, $request);
        $this->handleUploads($product, $request);

        return redirect()->route('admin.products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return back()->with('status', 'Product deleted successfully.');
    }

    public function deleteImage(ProductImage $image): RedirectResponse
    {
        Gate::authorize('products.manage');

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Screenshot removed.');
    }

    public function updateStatus(Product $product): RedirectResponse
    {
        $product->update(['status' => $product->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'Product status updated.');
    }

    public function toggleFeatured(Product $product): RedirectResponse
    {
        $product->update(['featured' => ! $product->featured]);

        return back()->with('status', 'Homepage visibility updated.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        // Normalise the two slug-shaped fields before validation so the `unique`
        // rules check the value that will actually be stored. A blank key falls
        // back to the current key (edit) / the slug (create, via the model hook).
        $request->merge([
            'slug' => filled($request->input('slug')) ? Str::slug($request->input('slug')) : null,
            'key' => filled($request->input('key'))
                ? Str::slug($request->input('key'))
                : $product?->key,
        ]);

        $validated = $request->validate([
            // Physical products are accepted by the schema but not yet manageable here —
            // they arrive with the inventory module, so the form only offers "software".
            'type' => ['required', Rule::in([Product::TYPE_SOFTWARE])],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'icon' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product?->id)],
            'key' => ['nullable', 'string', 'max:255', Rule::unique('products', 'key')->ignore($product?->id)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_unit' => ['nullable', 'string', 'max:50'],
            'pricing_note' => ['nullable', 'string', 'max:255'],
            'demo_url' => ['nullable', 'url', 'max:255'],
            'docs_url' => ['nullable', 'url', 'max:255'],
            'version' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'max:2048'],
            'screenshots' => ['nullable', 'array'],
            'screenshots.*' => ['image', 'max:2048'],
            'features_icon' => ['nullable', 'array'],
            'features_icon.*' => ['nullable', 'string', 'max:100'],
            'features_text' => ['nullable', 'array'],
            'features_text.*' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['featured'] = $request->boolean('featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        unset($validated['image'], $validated['screenshots'], $validated['features_icon'], $validated['features_text']);

        return $validated;
    }

    private function syncFeatures(Product $product, Request $request): void
    {
        $icons = $request->input('features_icon', []);
        $texts = $request->input('features_text', []);

        $product->features()->delete();

        foreach ($texts as $index => $text) {
            $text = trim((string) $text);

            if ($text === '') {
                continue;
            }

            $product->features()->create([
                'icon' => trim((string) ($icons[$index] ?? '')) ?: 'bi-check-circle',
                'text' => $text,
                'sort_order' => $index,
            ]);
        }
    }

    private function handleUploads(Product $product, Request $request): void
    {
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->update(['image' => $request->file('image')->store('products', 'public')]);
        }

        if ($request->hasFile('screenshots')) {
            $position = (int) $product->images()->max('sort_order');

            foreach ($request->file('screenshots') as $file) {
                $product->images()->create([
                    'path' => $file->store('products/screenshots', 'public'),
                    'sort_order' => ++$position,
                ]);
            }
        }
    }
}
