<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->get('sort'), ['full_name', 'service', 'status', 'priority', 'created_at'])
            ? $request->get('sort')
            : 'created_at';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $consultations = Consultation::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->get('search');
                $query->where(fn ($q) => $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->get('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->get('priority')))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.consultations.index', compact('consultations', 'sort', 'direction'));
    }

    public function edit(Consultation $consultation): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.pages.consultations.edit', compact('consultation', 'users'));
    }

    public function update(Request $request, Consultation $consultation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Consultation::STATUSES))],
            'priority' => ['required', Rule::in(array_keys(Consultation::PRIORITIES))],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $consultation->update($validated);

        return back()->with('status', 'Consultation request updated.');
    }

    public function destroy(Consultation $consultation): RedirectResponse
    {
        if ($consultation->attachment) {
            Storage::disk('public')->delete($consultation->attachment);
        }

        $consultation->delete();

        return redirect()->route('admin.consultations.index')->with('status', 'Consultation request deleted.');
    }
}
