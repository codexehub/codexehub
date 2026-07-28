<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:50'],

            'service' => ['required', 'string', 'max:255'],
            'project_type' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'budget' => ['nullable', 'string', 'max:100'],
            'timeline' => ['nullable', 'string', 'max:100'],

            'contact_method' => ['required', 'string', 'max:50'],
            'meeting_date' => ['nullable', 'date'],
            'meeting_time' => ['nullable', 'string', 'max:20'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,zip', 'max:25600'],
            'agreement' => ['accepted'],
        ]);

        unset($validated['agreement']);

        $validated['language'] = $validated['language'] ?? 'English';
        $validated['source'] = 'website';
        $validated['ip_address'] = $request->ip();
        $validated['browser'] = $request->userAgent();

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('consultations/attachments', 'public');
        }

        $nextId = (Consultation::max('id') ?? 0) + 1;
        $validated['reference_no'] = 'CH-'.str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);

        $consultation = Consultation::create($validated);

        return response()->json([
            'success' => true,
            'reference_no' => $consultation->reference_no,
            'message' => 'Your consultation request has been submitted successfully.',
        ]);
    }
}
