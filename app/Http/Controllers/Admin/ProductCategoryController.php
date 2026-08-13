<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:product.view', only: ['index']),
            new Middleware('can:product.create', only: ['store']),
            new Middleware('can:product.edit', only: ['edit', 'update']),
            new Middleware('can:product.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $categories = ProductCategory::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->paginate(15);

        return view('admin.pages.products.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in([Product::TYPE_SOFTWARE, Product::TYPE_PHYSICAL])],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ProductCategory::create($validated);

        return back()->with('status', 'Category added successfully.');
    }

    public function edit(ProductCategory $category): View
    {
        return view('admin.pages.products.categories.edit', compact('category'));
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('product_categories', 'slug')->ignore($category->id)],
            'type' => ['required', Rule::in([Product::TYPE_SOFTWARE, Product::TYPE_PHYSICAL])],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category->update($validated);

        return redirect()->route('admin.products.categories.index')->with('status', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'This category still has products assigned to it and cannot be deleted.']);
        }

        $category->delete();

        return back()->with('status', 'Category deleted successfully.');
    }
}
