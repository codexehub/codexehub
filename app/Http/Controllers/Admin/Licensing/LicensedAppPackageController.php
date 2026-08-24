<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\LicensedApp;
use App\Models\LicensedAppPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicensedAppPackageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.licensing.packages.index', [
            'packages' => LicensedAppPackage::with('licensedApp')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.licensing.packages.create', [
            'package' => new LicensedAppPackage(),
            'licensedApps' => LicensedApp::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        LicensedAppPackage::create($this->validated($request));

        return redirect()->route('admin.licensing.packages.index')->with('status', 'Package created.');
    }

    public function edit(LicensedAppPackage $package): View
    {
        return view('admin.pages.licensing.packages.edit', [
            'package' => $package,
            'licensedApps' => LicensedApp::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, LicensedAppPackage $package): RedirectResponse
    {
        $package->update($this->validated($request));

        return redirect()->route('admin.licensing.packages.index')->with('status', 'Package updated.');
    }

    public function destroy(LicensedAppPackage $package): RedirectResponse
    {
        $package->delete();

        return redirect()->route('admin.licensing.packages.index')->with('status', 'Package deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'licensed_app_id' => ['required', 'exists:licensed_apps,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_activations' => ['required', 'integer', 'min:1'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['features'] = $data['features']
            ? array_map('trim', explode(',', $data['features']))
            : [];

        return $data;
    }
}
