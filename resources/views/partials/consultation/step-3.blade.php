<fieldset class="consultation-step" data-consultation-step="3">
    <legend class="visually-hidden">Meeting Preferences</legend>
    <h3 class="consultation-step-title"><i class="bi bi-calendar2-week"></i> Meeting Preferences</h3>

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Preferred Contact Method <span class="text-danger">*</span></label>
            <div class="consultation-pill-group" data-consultation-pill-group="contact_method">
                @foreach ($contactMethods as $index => $method)
                    <input type="radio" class="btn-check" name="contact_method" id="cf-contact-{{ $index }}" value="{{ $method['value'] }}" autocomplete="off" required data-consultation-required @checked($index === 0)>
                    <label class="consultation-pill" for="cf-contact-{{ $index }}"><i class="bi {{ $method['icon'] }}"></i> {{ $method['value'] }}</label>
                @endforeach
            </div>
            <div class="invalid-feedback" data-consultation-error="contact_method"></div>
        </div>

        <div class="col-md-4">
            <label for="cf-meeting-date" class="form-label">Preferred Meeting Date</label>
            <input type="date" class="form-control" id="cf-meeting-date" name="meeting_date" min="{{ now()->toDateString() }}">
        </div>
        <div class="col-md-4">
            <label for="cf-meeting-time" class="form-label">Preferred Meeting Time</label>
            <input type="time" class="form-control" id="cf-meeting-time" name="meeting_time">
        </div>
        <div class="col-md-4">
            <label for="cf-timezone" class="form-label">Time Zone</label>
            <select class="form-select" id="cf-timezone" name="timezone">
                <option value="">Select time zone</option>
                @foreach ($timezones as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Attachment <small class="text-secondary">(PDF, DOCX, XLSX, PPTX, PNG, JPG, ZIP — max 25MB)</small></label>
            <div class="consultation-file-drop" data-consultation-file-drop>
                <label for="cf-attachment" class="consultation-file-label">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span data-consultation-file-name>Click to upload or drag a file here</span>
                </label>
                <input type="file" id="cf-attachment" name="attachment" class="consultation-file-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.zip" data-consultation-file-input>
            </div>
            <div class="invalid-feedback" data-consultation-error="attachment"></div>
        </div>

        <div class="col-12">
            <div class="form-check consultation-agreement">
                <input class="form-check-input" type="checkbox" id="cf-agreement" name="agreement" value="1" required data-consultation-required>
                <label class="form-check-label" for="cf-agreement">
                    I agree to the <a href="#" target="_blank">Privacy Policy</a> and <a href="#" target="_blank">Terms of Service</a>.
                </label>
                <div class="invalid-feedback" data-consultation-error="agreement"></div>
            </div>
        </div>
    </div>
</fieldset>
