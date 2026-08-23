export function initTeamModal() {
    const modalEl = document.getElementById('teamMemberModal');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        if (!button) return;

        const { teamName, teamRole, teamPhoto, teamDescription, teamLinkedin, teamGithub, teamImages } = button.dataset;

        modalEl.querySelector('[data-modal-name]').textContent = teamName || '';
        modalEl.querySelector('[data-modal-role]').textContent = teamRole || '';
        modalEl.querySelector('[data-modal-description]').textContent = teamDescription || '';

        const photoEl = modalEl.querySelector('[data-modal-photo]');
        if (teamPhoto) {
            photoEl.src = teamPhoto;
            photoEl.alt = teamName || '';
            photoEl.classList.remove('d-none');
        } else {
            photoEl.classList.add('d-none');
            photoEl.removeAttribute('src');
        }

        const linkedinEl = modalEl.querySelector('[data-modal-linkedin]');
        linkedinEl.classList.toggle('d-none', !teamLinkedin);
        if (teamLinkedin) linkedinEl.href = teamLinkedin;

        const githubEl = modalEl.querySelector('[data-modal-github]');
        githubEl.classList.toggle('d-none', !teamGithub);
        if (teamGithub) githubEl.href = teamGithub;

        const galleryEl = modalEl.querySelector('[data-modal-gallery]');
        galleryEl.innerHTML = '';

        let images = [];
        try {
            images = teamImages ? JSON.parse(teamImages) : [];
        } catch (e) {
            images = [];
        }

        if (images.length) {
            images.forEach((src) => {
                const img = document.createElement('img');
                img.src = src;
                img.alt = '';
                img.className = 'team-modal-gallery-img';
                galleryEl.appendChild(img);
            });
            galleryEl.classList.remove('d-none');
        } else {
            galleryEl.classList.add('d-none');
        }
    });
}
