<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:customers.view', only: ['index', 'show']),
            new Middleware('can:customers.manage', only: ['create', 'store', 'edit', 'update', 'destroy', 'updateStatus']),
        ];
    }

    public function index(Request $request): View
    {
        $sort = in_array($request->get('sort'), ['name', 'company_name', 'email', 'status', 'created_at'], true)
            ? $request->get('sort')
            : 'created_at';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $customers = Customer::query()
            ->withCount('licenses')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->get('search');
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when(
                $request->filled('status') && in_array($request->get('status'), Customer::STATUSES, true),
                fn ($query) => $query->where('status', $request->get('status'))
            )
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.customers.index', compact('customers', 'sort', 'direction'));
    }

    /**
     * The tabbed customer detail page (SRS §77). CUST-003 ships the shell — the
     * Information / License / Audit tabs render real data; every other tab is a
     * placeholder that fills in as its phase lands.
     */
    public function show(Customer $customer): View
    {
        $customer->loadCount('licenses');

        $licenses = $customer->licenses()
            ->with(['licensedApp', 'package'])
            ->orderByDesc('licenses.created_at')
            ->get();

        $auditLogs = AuditLog::forEntity($customer)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('admin.pages.customers.show', compact('customer', 'licenses', 'auditLogs'));
    }

    public function create(): View
    {
        return view('admin.pages.customers.create', ['customer' => new Customer(['status' => Customer::STATUS_PROSPECT])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::create($this->validateCustomer($request));

        return redirect()->route('admin.customers.index')->with('status', 'Customer created.');
    }

    public function edit(Customer $customer): View
    {
        $customer->loadCount('licenses');

        return view('admin.pages.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validateCustomer($request, $customer));

        return redirect()->route('admin.customers.index')->with('status', 'Customer updated.');
    }

    public function updateStatus(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Customer::STATUSES)],
        ]);

        $customer->update($validated);

        return back()->with('status', "{$customer->name} is now ".ucfirst($validated['status']).'.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->licenses()->exists()) {
            return back()->withErrors(['customer' => 'Cannot delete a customer that still has licenses. Revoke them first.']);
        }

        $customer->delete();

        return back()->with('status', 'Customer deleted.');
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(Customer::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
