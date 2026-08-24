<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\LicenseCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseCustomerController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.licensing.customers.index', [
            'customers' => LicenseCustomer::withCount('licenses')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.licensing.customers.create', ['customer' => new LicenseCustomer()]);
    }

    public function store(Request $request): RedirectResponse
    {
        LicenseCustomer::create($this->validated($request));

        return redirect()->route('admin.licensing.customers.index')->with('status', 'Customer created.');
    }

    public function edit(LicenseCustomer $customer): View
    {
        return view('admin.pages.licensing.customers.edit', ['customer' => $customer]);
    }

    public function update(Request $request, LicenseCustomer $customer): RedirectResponse
    {
        $customer->update($this->validated($request));

        return redirect()->route('admin.licensing.customers.index')->with('status', 'Customer updated.');
    }

    public function destroy(LicenseCustomer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('admin.licensing.customers.index')->with('status', 'Customer deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
