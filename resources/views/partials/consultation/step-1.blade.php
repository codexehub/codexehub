<fieldset class="consultation-step is-active" data-consultation-step="1">
    <legend class="visually-hidden">Personal Information</legend>
    <h3 class="consultation-step-title"><i class="bi bi-person-vcard"></i> Personal Information</h3>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="cf-full-name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cf-full-name" name="full_name" required maxlength="255" data-consultation-required>
            <div class="invalid-feedback" data-consultation-error="full_name"></div>
        </div>
        <div class="col-md-6">
            <label for="cf-company" class="form-label">Company Name</label>
            <input type="text" class="form-control" id="cf-company" name="company_name" maxlength="255">
            <div class="invalid-feedback" data-consultation-error="company_name"></div>
        </div>
        <div class="col-md-6">
            <label for="cf-email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="cf-email" name="email" required maxlength="255" data-consultation-required data-consultation-type="email">
            <div class="invalid-feedback" data-consultation-error="email"></div>
        </div>
        <div class="col-md-6">
            <label for="cf-phone" class="form-label">Phone / WhatsApp <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" id="cf-phone" name="phone" required maxlength="50" data-consultation-required data-consultation-type="phone">
            <div class="invalid-feedback" data-consultation-error="phone"></div>
        </div>
        <div class="col-md-6">
            <label for="cf-country" class="form-label">Country <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cf-country" name="country" required maxlength="255" data-consultation-required>
            <div class="invalid-feedback" data-consultation-error="country"></div>
        </div>
        <div class="col-md-6">
            <label for="cf-city" class="form-label">City</label>
            <input type="text" class="form-control" id="cf-city" name="city" maxlength="255">
            <div class="invalid-feedback" data-consultation-error="city"></div>
        </div>
        <div class="col-md-6">
            <label for="cf-language" class="form-label">Preferred Language</label>
            <select class="form-select" id="cf-language" name="language">
                @foreach ($languages as $language)
                    <option value="{{ $language }}" @selected($language === 'English')>{{ $language }}</option>
                @endforeach
            </select>
        </div>
    </div>
</fieldset>
