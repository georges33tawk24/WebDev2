/**
 * Updates request status badges when live-update events arrive.
 */
export function initLiveRequestTracking() {
    window.addEventListener('live-update', (event) => {
        const detail = event.detail || {};

        if (Array.isArray(detail.requests)) {
            applyRequestRows(detail.requests, 'citizen');
        }

        if (Array.isArray(detail.staff_requests)) {
            applyRequestRows(detail.staff_requests, 'staff');
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function applyRequestRows(rows, mode) {
    rows.forEach((row) => {
        const id = String(row.id);
        const statusLabel = row.status_label || row.status || '';

        document.querySelectorAll(`[data-live-request-id="${id}"]`).forEach((el) => {
            const statusEl = el.querySelector('[data-live-request-status]');

            if (statusEl) {
                statusEl.textContent = statusLabel;
            }

            const paidEl = el.querySelector('[data-live-request-paid]');

            if (paidEl && typeof row.is_paid === 'boolean') {
                paidEl.textContent = paidEl.dataset.paidLabel || 'Paid';
                paidEl.hidden = !row.is_paid;
                const unpaidEl = el.querySelector('[data-live-request-unpaid]');

                if (unpaidEl) {
                    unpaidEl.hidden = row.is_paid;
                }
            }
        });

        if (mode === 'staff') {
            const cell = document.querySelector(`[data-live-staff-request-status="${id}"]`);

            if (cell) {
                const slug = String(row.status || '').replace(/_/g, '-');
                cell.innerHTML =
                    '<span class="badge badge-' +
                    slug +
                    '">' +
                    escapeHtml(statusLabel) +
                    '</span>';
            }
        }
    });
}
