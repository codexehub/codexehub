<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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
            'license' => new License,
            'customers' => Customer::orderBy('name')->get(),
            'licensedApps' => LicensedApp::with('packages')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'licensed_app_id' => ['required', 'exists:licensed_apps,id'],
            'licensed_app_package_id' => ['required', 'exists:licensed_app_packages,id'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['license_customer_id'] = $this->resolveLicenseCustomer($data['customer_id'])->id;
        $data['status'] = 'pending';
        unset($data['customer_id']);

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
        $license->loadMissing('customer');

        return view('admin.pages.licensing.licenses.edit', [
            'license' => $license,
            'selectedCustomerId' => $license->customer?->customer_id,
            'customers' => Customer::orderBy('name')->get(),
            'licensedApps' => LicensedApp::with('packages')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, License $license): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'licensed_app_id' => ['required', 'exists:licensed_apps,id'],
            'licensed_app_package_id' => ['required', 'exists:licensed_app_packages,id'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['license_customer_id'] = $this->resolveLicenseCustomer($data['customer_id'])->id;
        unset($data['customer_id']);

        $license->update($data);

        return redirect()->route('admin.licensing.licenses.show', $license)->with('status', 'License updated.');
    }

    /**
     * Bridge the canonical Customer (CUST-001) to the released `licenses.license_customer_id`
     * FK: reuse the customer's licensing link row, or create one on first use. LIC-CP-001
     * reshapes `licenses` to point at `customers` directly and retires this.
     */
    private function resolveLicenseCustomer(int $customerId): LicenseCustomer
    {
        $customer = Customer::findOrFail($customerId);

        return LicenseCustomer::firstOrCreate(
            ['customer_id' => $customer->id],
            ['name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone],
        );
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
