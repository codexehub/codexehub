export function initContactForm() {
    const form = document.querySelector('[data-contact-form]');
    if (!form) return;

    const submitBtn = form.querySelector('[data-contact-submit]');
    const submitText = form.querySelector('[data-contact-submit-text]');
    const spinner = form.querySelector('[data-contact-spinner]');
    const successEl = document.querySelector('[data-contact-success]');
    const errorEl = document.querySelector('[data-contact-error]');

    const clearErrors = () => {
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('[data-contact-field-error]').forEach((el) => { el.textContent = ''; });
        successEl.classList.add('d-none');
        errorEl.classList.add('d-none');
    };

    const setFieldError = (name, message) => {
        const el = form.querySelector(`[data-contact-field-error="${name}"]`);
        if (el) el.textContent = message;
        form.querySelectorAll(`[name="${name}"]`).forEach((input) => input.classList.add('is-invalid'));
    };

    const setSubmitting = (submitting) => {
        submitBtn.disabled = submitting;
        spinner?.classList.toggle('d-none', !submitting);
        submitText?.classList.toggle('d-none', submitting);
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearErrors();
        setSubmitting(true);

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json' },
        })
            .then(async (response) => ({ status: response.status, body: await response.json() }))
            .then(({ status, body }) => {
                if (status === 200 && body.success) {
                    successEl.textContent = body.message;
                    successEl.classList.remove('d-none');
                    form.reset();
                    return;
                }

                if (status === 422 && body.errors) {
                    Object.entries(body.errors).forEach(([field, messages]) => {
                        setFieldError(field, messages[0]);
                    });
                    errorEl.classList.remove('d-none');
                    return;
                }

                errorEl.classList.remove('d-none');
            })
            .catch(() => {
                errorEl.classList.remove('d-none');
            })
            .finally(() => setSubmitting(false));
    });
}
