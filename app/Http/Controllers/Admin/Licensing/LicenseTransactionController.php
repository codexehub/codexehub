<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseTransaction;
use App\Services\Payments\PaymentProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseTransactionController extends Controller
{
    public function __construct(private PaymentProvider $payments)
    {
    }

    public function index(): View
    {
        return view('admin.pages.licensing.transactions.index', [
            'transactions' => LicenseTransaction::with('license.customer', 'license.licensedApp', 'approver')
                ->latest()
                ->paginate(20),
        ]);
    }

    /**
     * Record a bKash transaction ID the customer sent you, against a license.
     */
    public function store(Request $request, License $license): RedirectResponse
    {
        $data = $request->validate([
            'txn_id' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->payments->record($license, $data['txn_id'], (float) $data['amount'], $data['notes'] ?? null);

        return back()->with('status', 'Transaction recorded as pending. Approve it once you have confirmed the bKash payment.');
    }

    /**
     * Approve a recorded transaction: activates the license for the
     * package's duration. This is the "manually update from Codexehub" step.
     */
    public function approve(Request $request, LicenseTransaction $transaction): RedirectResponse
    {
        if ($transaction->status !== 'pending') {
            return back()->withErrors(['status' => 'This transaction has already been processed.']);
        }

        $this->payments->approve($transaction, $request->user());

        return back()->with('status', 'Payment approved -- license is now active.');
    }

    public function reject(LicenseTransaction $transaction): RedirectResponse
    {
        if ($transaction->status !== 'pending') {
            return back()->withErrors(['status' => 'This transaction has already been processed.']);
        }

        $transaction->update(['status' => 'rejected']);

        return back()->with('status', 'Transaction rejected.');
    }
}
