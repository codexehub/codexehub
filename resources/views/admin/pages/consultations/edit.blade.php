@extends('admin.layouts.app', ['title' => 'Consultation Request'])

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Consultation Request <code>{{ $consultation->reference_no }}</code></h1>
            <p>Submitted {{ $consultation->created_at->format('M d, Y \a\t g:i A') }} via {{ ucfirst($consultation->source) }}.</p>
        </div>
        <a href="{{ route('admin.consultations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-panel">
                <div class="admin-panel-header">
                    <h2>Personal Information</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><strong>Full Name</strong><div>{{ $consultation->full_name }}</div></div>
                    <div class="col-md-6"><strong>Company</strong><div>{{ $consultation->company_name ?: '—' }}</div></div>
                    <div class="col-md-6"><strong>Email</strong><div><a href="mailto:{{ $consultation->email }}">{{ $consultation->email }}</a></div></div>
                    <div class="col-md-6"><strong>Phone / WhatsApp</strong><div><a href="tel:{{ $consultation->phone }}">{{ $consultation->phone }}</a></div></div>
                    <div class="col-md-4"><strong>Country</strong><div>{{ $consultation->country }}</div></div>
                    <div class="col-md-4"><strong>City</strong><div>{{ $consultation->city ?: '—' }}</div></div>
                    <div class="col-md-4"><strong>Preferred Language</strong><div>{{ $consultation->language }}</div></div>
                </div>
            </div>

            <div class="admin-panel">
                <div class="admin-panel-header">
                    <h2>Project Information</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><strong>Service Required</strong><div>{{ $consultation->service }}</div></div>
                    <div class="col-md-4"><strong>Project Type</strong><div>{{ $consultation->project_type ?: '—' }}</div></div>
                    <div class="col-md-4"><strong>Budget Range</strong><div>{{ $consultation->budget ?: '—' }}</div></div>
                    <div class="col-md-4"><strong>Expected Timeline</strong><div>{{ $consultation->timeline ?: '—' }}</div></div>
                    <div class="col-12">
                        <strong>Project Description</strong>
                        <p class="mb-0">{{ $consultation->description }}</p>
                    </div>
                </div>
            </div>

            <div class="admin-panel">
                <div class="admin-panel-header">
                    <h2>Meeting Preferences</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-3"><strong>Contact Method</strong><div>{{ ucfirst(str_replace('_', ' ', $consultation->contact_method)) }}</div></div>
                    <div class="col-md-3"><strong>Meeting Date</strong><div>{{ $consultation->meeting_date?->format('M d, Y') ?: '—' }}</div></div>
                    <div class="col-md-3"><strong>Meeting Time</strong><div>{{ $consultation->meeting_time ?: '—' }}</div></div>
                    <div class="col-md-3"><strong>Time Zone</strong><div>{{ $consultation->timezone ?: '—' }}</div></div>
                    <div class="col-12">
                        <strong>Attachment</strong>
                        <div>
                            @if ($consultation->attachment)
                                <a href="{{ $consultation->attachment_url }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> {{ basename($consultation->attachment) }}</a>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-panel">
                <div class="admin-panel-header">
                    <h2>Manage</h2>
                </div>
                <form method="POST" action="{{ route('admin.consultations.update', $consultation) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            @foreach (\App\Models\Consultation::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $consultation->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select">
                            @foreach (\App\Models\Consultation::PRIORITIES as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', $consultation->priority) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assigned To</label>
                        <select id="assigned_to" name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('assigned_to', $consultation->assigned_to) === (string) $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Internal Notes</label>
                        <textarea id="notes" name="notes" rows="5" class="form-control">{{ old('notes', $consultation->notes) }}</textarea>
                        @error('notes') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>

            <div class="admin-panel">
                <div class="admin-panel-header">
                    <h2>Meta</h2>
                </div>
                <div class="row g-2 small text-secondary">
                    <div class="col-12"><strong class="text-body">Source:</strong> {{ ucfirst($consultation->source) }}</div>
                    <div class="col-12"><strong class="text-body">IP Address:</strong> {{ $consultation->ip_address ?: '—' }}</div>
                    <div class="col-12"><strong class="text-body">Browser:</strong> <span class="text-break">{{ $consultation->browser ?: '—' }}</span></div>
                </div>
            </div>
        </div>
    </div>
@endsection
