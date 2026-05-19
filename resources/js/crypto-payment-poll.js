export function initCryptoPaymentPoll(config) {
    const statusEl = document.getElementById('crypto-payment-status');
    const pollUrl = config.pollUrl;
    const paymentsUrl = config.paymentsUrl || '/citizen/payments';

    if (!pollUrl || !statusEl) {
        return;
    }

    let attempts = 0;
    let stopped = false;
    const intervalMs = Number(config.intervalMs) || 3000;
    const maxAttempts = Number(config.maxAttempts) || 120;

    function showStatus(kind, message) {
        statusEl.hidden = false;
        statusEl.dataset.kind = kind;
        statusEl.textContent = message;

        const styles = {
            pending: { background: '#eff6ff', color: '#1e40af' },
            confirming: { background: '#eff6ff', color: '#1e40af' },
            success: { background: '#ecfdf5', color: '#065f46' },
            error: { background: '#fee2e2', color: '#991b1b' },
            warning: { background: '#fffbeb', color: '#92400e' },
        };

        Object.assign(statusEl.style, styles[kind] || styles.pending);
        statusEl.style.padding = '14px 16px';
        statusEl.style.borderRadius = '10px';
        statusEl.style.marginBottom = '20px';
        statusEl.style.fontSize = '14px';
    }

    async function poll() {
        if (stopped) {
            return;
        }

        attempts += 1;

        try {
            const response = await fetch(pollUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('status poll failed');
            }

            const data = await response.json();

            if (data.state === 'paid') {
                stopped = true;
                showStatus('success', config.paidLabel || 'Payment confirmed. Redirecting…');
                window.setTimeout(() => {
                    window.location.href = data.redirect_url || paymentsUrl;
                }, 800);

                return;
            }

            if (data.state === 'failed') {
                stopped = true;
                showStatus('error', data.message || config.failedLabel || 'Payment failed.');

                return;
            }

            if (data.state === 'confirming') {
                showStatus('confirming', data.message || config.confirmingLabel || 'Confirming payment…');
            } else {
                showStatus('pending', config.pendingLabel || 'Waiting for payment confirmation…');
            }

            if (attempts >= maxAttempts) {
                stopped = true;
                showStatus('warning', config.timeoutLabel || 'Still waiting. Try opening checkout again or contact support.');

                return;
            }

            window.setTimeout(poll, intervalMs);
        } catch {
            if (attempts >= maxAttempts) {
                stopped = true;
                showStatus('warning', config.timeoutLabel || 'Could not verify payment. Refresh the page or try again.');

                return;
            }

            window.setTimeout(poll, intervalMs + 2000);
        }
    }

    if (config.returnedFromGateway) {
        showStatus('pending', config.returnedLabel || 'Thanks — confirming your payment automatically…');
    } else {
        showStatus('pending', config.pendingLabel || 'Waiting for payment confirmation…');
    }

    poll();

    document.addEventListener('visibilitychange', () => {
        if (!stopped && document.visibilityState === 'visible') {
            poll();
        }
    });

    const checkNow = document.getElementById('crypto-check-now');
    if (checkNow) {
        checkNow.addEventListener('click', (event) => {
            event.preventDefault();
            if (!stopped) {
                poll();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('crypto-payment-poll-root');
    if (!root || !root.dataset.config) {
        return;
    }

    try {
        initCryptoPaymentPoll(JSON.parse(root.dataset.config));
    } catch {
        // ignore invalid config
    }
});
