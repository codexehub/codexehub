@csrf
@if ($license->exists)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-12">
        <label for="license_customer_id" class="form-label">Customer</label>
        <select name="license_customer_id" id="license_customer_id" class="form-select @error('license_customer_id') is-invalid @enderror" required>
            <option value="">Select a customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('license_customer_id', $license->license_customer_id) == $customer->id)>{{ $customer->name }} ({{ $customer->email }})</option>
            @endforeach
        </select>
        @error('license_customer_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Not listed yet? <a href="{{ route('admin.licensing.customers.create') }}">Add a customer</a> first.</small>
    </div>

    <div class="col-md-12">
        <label for="licensed_app_id" class="form-label">Licensed App</label>
        <select name="licensed_app_id" id="licensed_app_id" class="form-select @error('licensed_app_id') is-invalid @enderror" required>
            <option value="">Select an app</option>
            @foreach ($licensedApps as $app)
                <option value="{{ $app->id }}" data-packages="{{ $app->packages->pluck('id')->implode(',') }}" @selected(old('licensed_app_id', $license->licensed_app_id) == $app->id)>{{ $app->name }}</option>
            @endforeach
        </select>
        @error('licensed_app_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="licensed_app_package_id" class="form-label">Package</label>
        <select name="licensed_app_package_id" id="licensed_app_package_id" class="form-select @error('licensed_app_package_id') is-invalid @enderror" required>
            <option value="">Select an app first</option>
            @foreach ($licensedApps as $app)
                @foreach ($app->packages as $package)
                    <option value="{{ $package->id }}" data-app="{{ $app->id }}" @selected(old('licensed_app_package_id', $license->licensed_app_package_id) == $package->id)>{{ $app->name }} - {{ $package->name }} ({{ $package->duration_days }}d, {{ $package->max_activations }} activation(s))</option>
                @endforeach
            @endforeach
        </select>
        @error('licensed_app_package_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="notes" class="form-label">Notes</label>
        <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $license->notes) }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @unless ($license->exists)
        <div class="col-md-12">
            <p class="text-secondary small mb-0">The license is created with status <strong>pending</strong> until you record and approve a payment.</p>
        </div>
    @endunless
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.licensing.licenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

<script>
    // Filter the package dropdown down to the selected app's packages.
    const appSelect = document.getElementById('licensed_app_id');
    const packageSelect = document.getElementById('licensed_app_package_id');
    const packageOptions = Array.from(packageSelect.options);

    function filterPackages() {
        const appId = appSelect.value;
        packageSelect.innerHTML = '';
        packageSelect.appendChild(new Option('Select a package', ''));
        packageOptions.forEach(opt => {
            if (!opt.dataset.app) return;
            if (opt.dataset.app === appId) {
                packageSelect.appendChild(opt.cloneNode(true));
            }
        });
    }

    appSelect.addEventListener('change', filterPackages);
    if (appSelect.value) filterPackages();
</script>
