@csrf
@if ($customer->exists)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Contact Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="company_name" class="form-label">Company Name</label>
        <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $customer->company_name) }}" class="form-control @error('company_name') is-invalid @enderror">
        @error('company_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}" class="form-control @error('email') is-invalid @enderror" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}" class="form-control @error('phone') is-invalid @enderror">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach (\App\Models\Customer::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status', $customer->status ?? 'prospect') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if ($customer->exists)
        <div class="col-md-6 d-flex align-items-end">
            <p class="text-secondary small mb-2">{{ $customer->licenses_count ?? $customer->licenses()->count() }} license(s) attached.</p>
        </div>
    @endif

    <div class="col-md-12">
        <label for="notes" class="form-label">Notes</label>
        <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $customer->notes) }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ $customer->exists ? 'Update Customer' : 'Create Customer' }}</button>
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
