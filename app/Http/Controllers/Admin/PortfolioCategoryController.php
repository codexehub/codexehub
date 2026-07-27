<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortfolioCategoryController extends Controller
{
    public function index(): View
    {
        $categories = PortfolioCategory::query()
            ->withCount('portfolios')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.pages.portfolio.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        PortfolioCategory::create($validated);

        return back()->with('status', 'Category added successfully.');
    }

    public function edit(PortfolioCategory $category): View
    {
        return view('admin.pages.portfolio.categories.edit', compact('category'));
    }

    public function update(Request $request, PortfolioCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('portfolio_categories', 'slug')->ignore($category->id)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $category->update($validated);

        return redirect()->route('admin.portfolio.categories.index')->with('status', 'Category updated successfully.');
    }

    public function destroy(PortfolioCategory $category): RedirectResponse
    {
        if ($category->portfolios()->exists()) {
            return back()->withErrors(['category' => 'This category still has projects assigned to it and cannot be deleted.']);
        }

        $category->delete();

        return back()->with('status', 'Category deleted successfully.');
    }
}
