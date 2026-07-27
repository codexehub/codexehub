<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortfolioSettingController extends Controller
{
    public function edit(): View
    {
        $settings = PortfolioSetting::current();

        return view('admin.pages.portfolio.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_title' => ['required', 'string', 'max:255'],
            'section_subtitle' => ['nullable', 'string', 'max:255'],
            'projects_count' => ['required', Rule::in([6, 9, 12])],
            'ordering' => ['required', Rule::in(['newest', 'oldest', 'manual', 'featured_first'])],
        ]);

        $validated['section_visible'] = $request->boolean('section_visible');

        PortfolioSetting::current()->update($validated);

        return back()->with('status', 'Homepage portfolio settings updated.');
    }
}
