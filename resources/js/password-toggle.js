function bindPasswordToggle(btn) {
    if (btn.dataset.toggleBound === '1') {
        return;
    }
    btn.dataset.toggleBound = '1';

    const labels = window.passwordToggleLabels || {
        show: 'Show password',
        hide: 'Hide password',
    };

    btn.addEventListener('click', function () {
        const input = document.getElementById(btn.getAttribute('aria-controls'));
        if (!input) {
            return;
        }

        const isVisible = input.type === 'password';
        input.type = isVisible ? 'text' : 'password';
        btn.classList.toggle('is-password-visible', input.type === 'text');
        btn.setAttribute('aria-pressed', input.type === 'text' ? 'true' : 'false');
        btn.setAttribute('aria-label', input.type === 'text' ? labels.hide : labels.show);
    });
}

export function initPasswordToggles(root = document) {
    root.querySelectorAll('[data-toggle-pass]').forEach(bindPasswordToggle);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initPasswordToggles());
} else {
    initPasswordToggles();
}
