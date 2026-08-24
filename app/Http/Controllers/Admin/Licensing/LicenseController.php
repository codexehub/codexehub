<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.licensing.licenses.index', [
            'licenses' => License::with('customer', 'licensedApp', 'package')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.licensing.licenses.create', [
            'license' => new License(),
            'customers' => LicenseCustomer::orderBy('name')->get(),
            'licensedApps' => LicensedApp::with('packages')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'license_customer_id' => ['required', 'exists:license_customers,id'],
            'licensed_app_id' => ['required', 'exists:licensed_apps,id'],
            'licensed_app_package_id' => ['required', 'exists:licensed_app_packages,id'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['status'] = 'pending';

        $license = License::create($data);

        return redirect()->route('admin.licensing.licenses.show', $license)->with('status', 'License created (pending payment).');
    }

    public function show(License $license): View
    {
        $license->load('customer', 'licensedApp', 'package', 'activations', 'transactions.approver');

        return view('admin.pages.licensing.licenses.show', ['license' => $license]);
    }

    public function edit(License $license): View
    {
        return view('admin.pages.licensing.licenses.edit', [
            'license' => $license,
            'customers' => LicenseCustomer::orderBy('name')->get(),
            'licensedApps' => LicensedApp::with('packages')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, License $license): RedirectResponse
    {
        $data = $request->validate([
            'license_customer_id' => ['required', 'exists:license_customers,id'],
            'licensed_app_id' => ['required', 'exists:licensed_apps,id'],
            'licensed_app_package_id' => ['required', 'exists:licensed_app_packages,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $license->update($data);

        return redirect()->route('admin.licensing.licenses.show', $license)->with('status', 'License updated.');
    }

    public function suspend(License $license): RedirectResponse
    {
        $license->update(['status' => 'suspended']);

        return back()->with('status', 'License suspended. It will stop working on its next check-in.');
    }

    public function reactivate(License $license): RedirectResponse
    {
        if ($license->expires_at === null || $license->expires_at->isPast()) {
            return back()->withErrors(['status' => 'Cannot reactivate: license has no valid (future) expiry. Approve a new payment instead.']);
        }

        $license->update(['status' => 'active']);

        return back()->with('status', 'License reactivated.');
    }

    public function revoke(License $license): RedirectResponse
    {
        $license->update(['status' => 'revoked']);

        return back()->with('status', 'License permanently revoked.');
    }
}
