import * as bootstrapJs from 'bootstrap';
window.bootstrap = bootstrapJs;

import 'bootstrap-icons/font/bootstrap-icons.css';

import Chart from 'chart.js/auto';

function initSidebarToggles() {
    const shell = document.querySelector('.admin-shell');
    const collapseBtn = document.querySelector('[data-sidebar-collapse-toggle]');
    const mobileBtn = document.querySelector('[data-sidebar-mobile-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    collapseBtn?.addEventListener('click', () => {
        shell?.classList.toggle('admin-sidebar-collapsed');
    });

    const closeMobileSidebar = () => shell?.classList.remove('admin-sidebar-mobile-open');

    mobileBtn?.addEventListener('click', () => {
        shell?.classList.toggle('admin-sidebar-mobile-open');
    });

    backdrop?.addEventListener('click', closeMobileSidebar);
}

function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new window.bootstrap.Tooltip(el, { placement: 'right' });
    });
}

function initTagInputs() {
    document.querySelectorAll('[data-tag-input]').forEach((wrapper) => {
        const chipsEl = wrapper.querySelector('[data-tag-input-chips]');
        const fieldEl = wrapper.querySelector('[data-tag-input-field]');
        const valueEl = wrapper.querySelector('[data-tag-input-value]');

        let tags = (wrapper.dataset.initial || '')
            .split(',')
            .map((tag) => tag.trim())
            .filter(Boolean);

        const render = () => {
            valueEl.value = tags.join(',');
            chipsEl.innerHTML = '';
            tags.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'admin-tag-chip';
                chip.innerHTML = `${tag} <button type="button" aria-label="Remove ${tag}">&times;</button>`;
                chip.querySelector('button').addEventListener('click', () => {
                    tags.splice(index, 1);
                    render();
                });
                chipsEl.appendChild(chip);
            });
        };

        fieldEl.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                const value = fieldEl.value.trim();
                if (value && !tags.includes(value)) {
                    tags.push(value);
                    render();
                }
                fieldEl.value = '';
            }
        });

        render();
    });
}

function initSelectAll() {
    document.querySelectorAll('[data-select-all]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const name = checkbox.dataset.selectAll;
            document.querySelectorAll(`input[type="checkbox"][name="${name}"]`).forEach((box) => {
                box.checked = checkbox.checked;
            });
        });
    });
}

const CHART_COLORS = {
    primary: '#165E53',
    accent: '#9FC149',
};

function initCharts() {
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const type = canvas.dataset.chart;
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]');
        const color = CHART_COLORS[canvas.dataset.color] || CHART_COLORS.primary;

        const baseConfig = {
            labels,
            datasets: [{
                data: values,
                borderColor: color,
                backgroundColor: type === 'pie'
                    ? [CHART_COLORS.primary, CHART_COLORS.accent, '#2563EB', '#F59E0B', '#DC2626']
                    : `${color}33`,
                borderWidth: type === 'pie' ? 0 : 2,
                borderRadius: type === 'bar' ? 6 : 0,
                fill: type === 'area',
                tension: 0.4,
                pointRadius: 0,
            }],
        };

        new Chart(canvas, {
            type: type === 'area' ? 'line' : type,
            data: baseConfig,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: type === 'pie', position: 'bottom' },
                },
                scales: type === 'pie' ? {} : {
                    y: { beginAtZero: true, grid: { color: '#E5E7EB' } },
                    x: { grid: { display: false } },
                },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSidebarToggles();
    initTooltips();
    initCharts();
    initTagInputs();
    initSelectAll();
});
