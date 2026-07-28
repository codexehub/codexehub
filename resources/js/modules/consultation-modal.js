const TOTAL_STEPS = 3;

const FIELD_STEP_MAP = {
    full_name: 1, company_name: 1, email: 1, phone: 1, country: 1, city: 1, language: 1,
    service: 2, project_type: 2, description: 2, budget: 2, timeline: 2,
    contact_method: 3, meeting_date: 3, meeting_time: 3, timezone: 3, attachment: 3, agreement: 3,
};

const MAX_FILE_SIZE = 25 * 1024 * 1024;

export function initConsultationModal() {
    const modalEl = document.getElementById('consultationModal');
    if (!modalEl) return;

    const form = modalEl.querySelector('[data-consultation-form]');
    const steps = Array.from(modalEl.querySelectorAll('[data-consultation-step]'));
    const prevBtn = modalEl.querySelector('[data-consultation-prev]');
    const nextBtn = modalEl.querySelector('[data-consultation-next]');
    const submitBtn = modalEl.querySelector('[data-consultation-submit]');
    const spinner = modalEl.querySelector('[data-consultation-spinner]');
    const progressFill = modalEl.querySelector('[data-consultation-progress-fill]');
    const stepLabel = modalEl.querySelector('[data-consultation-step-label]');
    const footer = modalEl.querySelector('[data-consultation-footer]');
    const progressWrap = modalEl.querySelector('.consultation-progress');
    const successEl = modalEl.querySelector('[data-consultation-success]');
    const formError = modalEl.querySelector('[data-consultation-form-error]');
    const fileInput = modalEl.querySelector('[data-consultation-file-input]');
    const fileNameEl = modalEl.querySelector('[data-consultation-file-name]');

    let currentStep = 1;

    const showStep = (step) => {
        currentStep = step;

        steps.forEach((el) => {
            el.classList.toggle('is-active', Number(el.dataset.consultationStep) === step);
        });

        progressFill.style.width = `${(step / TOTAL_STEPS) * 100}%`;
        stepLabel.textContent = `Step ${step} of ${TOTAL_STEPS}`;

        prevBtn.classList.toggle('d-none', step === 1);
        nextBtn.classList.toggle('d-none', step === TOTAL_STEPS);
        submitBtn.classList.toggle('d-none', step !== TOTAL_STEPS);
    };

    const clearFieldError = (field) => {
        field.classList.remove('is-invalid');
        const errorEl = modalEl.querySelector(`[data-consultation-error="${field.name}"]`);
        if (errorEl) errorEl.textContent = '';
    };

    const setFieldError = (name, message, container = modalEl) => {
        const errorEl = modalEl.querySelector(`[data-consultation-error="${name}"]`);
        if (errorEl) errorEl.textContent = message;
        container.querySelectorAll(`[name="${name}"]`).forEach((el) => el.classList.add('is-invalid'));
    };

    const validateStep = (step) => {
        const stepEl = steps.find((el) => Number(el.dataset.consultationStep) === step);
        const fields = Array.from(stepEl.querySelectorAll('[data-consultation-required]'));
        const processedNames = new Set();
        let firstInvalid = null;

        fields.forEach((field) => {
            if (processedNames.has(field.name)) return;
            processedNames.add(field.name);

            clearFieldError(field);
            if (field.type === 'radio') {
                stepEl.querySelectorAll(`[name="${field.name}"]`).forEach((el) => el.classList.remove('is-invalid'));
            }

            let valid = true;
            let message = 'This field is required.';

            if (field.type === 'radio') {
                valid = Array.from(stepEl.querySelectorAll(`[name="${field.name}"]`)).some((el) => el.checked);
            } else if (field.type === 'checkbox') {
                valid = field.checked;
                message = 'You must accept to continue.';
            } else {
                valid = field.checkValidity();
                message = field.validity.typeMismatch ? 'Please enter a valid value.' : 'This field is required.';

                if (valid && field.dataset.consultationType === 'phone') {
                    valid = /^[+]?[\d\s()-]{7,20}$/.test(field.value.trim());
                    message = 'Please enter a valid phone number.';
                }
            }

            if (!valid) {
                setFieldError(field.name, message, stepEl);
                if (!firstInvalid) firstInvalid = field;
            }
        });

        if (firstInvalid) firstInvalid.focus();

        return !firstInvalid;
    };

    const resetFileDisplay = () => {
        if (fileNameEl) fileNameEl.textContent = 'Click to upload or drag a file here';
    };

    fileInput?.addEventListener('change', () => {
        const file = fileInput.files[0];
        clearFieldError(fileInput);

        if (!file) {
            resetFileDisplay();
            return;
        }

        if (file.size > MAX_FILE_SIZE) {
            setFieldError('attachment', 'File must be smaller than 25MB.');
            fileInput.value = '';
            resetFileDisplay();
            return;
        }

        if (fileNameEl) fileNameEl.textContent = file.name;
    });

    nextBtn?.addEventListener('click', () => {
        if (validateStep(currentStep)) {
            showStep(Math.min(currentStep + 1, TOTAL_STEPS));
        }
    });

    prevBtn?.addEventListener('click', () => {
        showStep(Math.max(currentStep - 1, 1));
    });

    const setSubmitting = (submitting) => {
        prevBtn.disabled = submitting;
        nextBtn.disabled = submitting;
        submitBtn.disabled = submitting;
        spinner?.classList.toggle('d-none', !submitting);
        submitBtn.querySelector('.consultation-btn-text')?.classList.toggle('d-none', submitting);
    };

    const showSuccess = (referenceNo) => {
        steps.forEach((el) => el.classList.remove('is-active'));
        footer.classList.add('d-none');
        progressWrap.classList.add('d-none');
        successEl.classList.remove('d-none');
        const refEl = modalEl.querySelector('[data-consultation-reference]');
        if (refEl) refEl.textContent = referenceNo;
    };

    const resetForm = () => {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('[data-consultation-error]').forEach((el) => { el.textContent = ''; });
        formError.classList.add('d-none');
        resetFileDisplay();
        footer.classList.remove('d-none');
        progressWrap.classList.remove('d-none');
        successEl.classList.add('d-none');
        showStep(1);
    };

    modalEl.addEventListener('hidden.bs.modal', resetForm);

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        if (!validateStep(TOTAL_STEPS)) return;

        formError.classList.add('d-none');
        setSubmitting(true);

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json' },
        })
            .then(async (response) => ({ status: response.status, body: await response.json() }))
            .then(({ status, body }) => {
                if (status === 200 && body.success) {
                    showSuccess(body.reference_no);
                    return;
                }

                if (status === 422 && body.errors) {
                    let earliestStep = TOTAL_STEPS;

                    Object.entries(body.errors).forEach(([field, messages]) => {
                        setFieldError(field, messages[0]);
                        earliestStep = Math.min(earliestStep, FIELD_STEP_MAP[field] || TOTAL_STEPS);
                    });

                    showStep(earliestStep);
                    formError.classList.remove('d-none');
                    return;
                }

                formError.classList.remove('d-none');
            })
            .catch(() => {
                formError.classList.remove('d-none');
            })
            .finally(() => setSubmitting(false));
    });

    showStep(1);
}
