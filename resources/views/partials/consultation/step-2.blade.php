<fieldset class="consultation-step" data-consultation-step="2">
    <legend class="visually-hidden">Project Information</legend>
    <h3 class="consultation-step-title"><i class="bi bi-kanban"></i> Project Information</h3>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="cf-service" class="form-label">Service Required <span class="text-danger">*</span></label>
            <select class="form-select" id="cf-service" name="service" required data-consultation-required>
                <option value="">Select a service</option>
                @foreach ($servicesList as $service)
                    <option value="{{ $service }}">{{ $service }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback" data-consultation-error="service"></div>
        </div>
        <div class="col-md-6">
            <label for="cf-project-type" class="form-label">Project Type</label>
            <select class="form-select" id="cf-project-type" name="project_type">
                <option value="">Select project type</option>
                @foreach ($projectTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label for="cf-description" class="form-label">Project Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="cf-description" name="description" rows="4" required maxlength="5000" data-consultation-required></textarea>
            <div class="invalid-feedback" data-consultation-error="description"></div>
        </div>
        <div class="col-md-6">
            <label for="cf-budget" class="form-label">Budget Range</label>
            <select class="form-select" id="cf-budget" name="budget">
                <option value="">Select budget range</option>
                @foreach ($budgets as $budget)
                    <option value="{{ $budget }}">{{ $budget }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label for="cf-timeline" class="form-label">Expected Timeline</label>
            <select class="form-select" id="cf-timeline" name="timeline">
                <option value="">Select timeline</option>
                @foreach ($timelines as $timeline)
                    <option value="{{ $timeline }}">{{ $timeline }}</option>
                @endforeach
            </select>
        </div>
    </div>
</fieldset>
