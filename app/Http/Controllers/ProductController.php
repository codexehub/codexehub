<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function software(Request $request): View
    {
        $categories = ProductCategory::query()
            ->ofType(Product::TYPE_SOFTWARE)
            ->orderBy('sort_order')
            ->get();

        $activeCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->get('category'))
            : null;

        $products = Product::query()
            ->active()
            ->software()
            ->when($activeCategory, fn ($query) => $query->where('product_category_id', $activeCategory->id))
            ->with('category')
            ->orderBy('sort_order')
            ->get();

        return view('pages.products', [
            'title' => 'Software Products — CodexeHub | Ready-to-Deploy Business Software',
            'description' => 'Browse CodexeHub\'s ready-to-deploy software products — ERP, POS, HR and SaaS platforms built for growing businesses.',
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    public function physical(): View
    {
        return view('pages.products-physical', [
            'title' => 'Hardware Products — CodexeHub',
            'description' => 'CodexeHub hardware and physical products are coming soon.',
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === 'active', 404);

        $product->load(['features', 'images', 'category']);

        $relatedProducts = Product::query()
            ->active()
            ->where('type', $product->type)
            ->where('id', '!=', $product->id)
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('pages.product-show', [
            'title' => "{$product->title} — CodexeHub Products",
            'description' => $product->short_description,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
