<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $categories = PortfolioCategory::active()->orderBy('name')->get();

        $portfolios = Portfolio::query()
            ->active()
            ->with(['category', 'technologies', 'images'])
            ->orderBy('sort_order')
            ->get();

        return view('pages.portfolio', [
            'title' => 'Portfolio — CodexeHub | Case Studies & Software Projects',
            'description' => 'Explore CodexeHub\'s portfolio of enterprise software, web platforms, mobile apps and AI solutions delivered for clients across industries.',
            'categories' => $categories,
            'portfolios' => $portfolios,
        ]);
    }

    public function show(Portfolio $portfolio): View
    {
        abort_unless($portfolio->status === 'active', 404);

        $portfolio->load(['category', 'technologies', 'images']);

        $relatedPortfolios = Portfolio::query()
            ->active()
            ->where('category_id', $portfolio->category_id)
            ->where('id', '!=', $portfolio->id)
            ->with(['category', 'images'])
            ->limit(3)
            ->get();

        return view('pages.portfolio-show', [
            'title' => "{$portfolio->title} — CodexeHub Portfolio",
            'description' => $portfolio->short_description,
            'portfolio' => $portfolio,
            'relatedPortfolios' => $relatedPortfolios,
        ]);
    }
}
