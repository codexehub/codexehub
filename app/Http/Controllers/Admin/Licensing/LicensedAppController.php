<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\LicensedApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicensedAppController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.licensing.apps.index', [
            'licensedApps' => LicensedApp::withCount('packages', 'licenses')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.licensing.apps.create', ['licensedApp' => new LicensedApp()]);
    }

    public function store(Request $request): RedirectResponse
    {
        LicensedApp::create($this->validated($request));

        return redirect()->route('admin.licensing.apps.index')->with('status', 'Licensed app created.');
    }

    public function edit(LicensedApp $app): View
    {
        return view('admin.pages.licensing.apps.edit', ['licensedApp' => $app]);
    }

    public function update(Request $request, LicensedApp $app): RedirectResponse
    {
        $app->update($this->validated($request));

        return redirect()->route('admin.licensing.apps.index')->with('status', 'Licensed app updated.');
    }

    public function destroy(LicensedApp $app): RedirectResponse
    {
        $app->delete();

        return redirect()->route('admin.licensing.apps.index')->with('status', 'Licensed app deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
