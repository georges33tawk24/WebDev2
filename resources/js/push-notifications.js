function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

function pushSupported() {
    return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
}

export async function initWebPush(config, { requestPermission = false } = {}) {
    if (!pushSupported()) {
        return { ok: false, reason: 'unsupported' };
    }

    const publicKeyUrl = config.publicKeyUrl;
    const subscribeUrl = config.subscribeUrl;
    const csrf = config.csrf;

    if (!publicKeyUrl || !subscribeUrl || !csrf) {
        return { ok: false, reason: 'config' };
    }

    try {
        const keyResponse = await fetch(publicKeyUrl, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        if (!keyResponse.ok) {
            return { ok: false, reason: 'api' };
        }

        const keyData = await keyResponse.json();

        if (!keyData.configured || !keyData.public_key) {
            return { ok: false, reason: 'not_configured' };
        }

        let registration;

        try {
            registration = await navigator.serviceWorker.register('/sw.js');
        } catch (error) {
            const message = error instanceof Error ? error.message : String(error);

            if (/ssl|certificate|secure origin/i.test(message)) {
                return { ok: false, reason: 'ssl_certificate' };
            }

            throw error;
        }

        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            if (Notification.permission === 'default') {
                if (!requestPermission) {
                    return { ok: false, reason: 'permission_default' };
                }

                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    return { ok: false, reason: permission === 'denied' ? 'permission_denied' : 'permission_blocked' };
                }
            } else if (Notification.permission !== 'granted') {
                return { ok: false, reason: 'permission_denied' };
            }

            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(keyData.public_key),
            });
        }

        const json = subscription.toJSON();

        const storeResponse = await fetch(subscribeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                endpoint: json.endpoint,
                keys: json.keys,
                contentEncoding: 'aesgcm',
            }),
        });

        if (!storeResponse.ok) {
            return { ok: false, reason: 'subscribe_failed' };
        }

        return { ok: true, reason: 'subscribed' };
    } catch (error) {
        const message = error instanceof Error ? error.message : String(error);

        if (/ssl|certificate|secure origin/i.test(message)) {
            return { ok: false, reason: 'ssl_certificate' };
        }

        return { ok: false, reason: 'error' };
    }
}

function updatePushBanner(config, result) {
    const banner = document.getElementById('push-enable-banner');

    if (!banner) {
        return;
    }

    const enableBtn = document.getElementById('push-enable-btn');
    const messageEl = document.getElementById('push-enable-message');

    if (!pushSupported()) {
        banner.hidden = true;

        return;
    }

    if (Notification.permission === 'granted' && result?.ok) {
        banner.hidden = true;

        return;
    }

    banner.hidden = false;

    if (result?.reason === 'ssl_certificate') {
        if (messageEl) {
            messageEl.textContent =
                config.sslLabel ||
                'Local HTTPS certificate is not trusted. Run "caddy trust" in Terminal, restart Chrome, then try again.';
        }

        if (enableBtn) {
            enableBtn.hidden = true;
        }

        return;
    }

    if (Notification.permission === 'denied') {
        if (messageEl) {
            messageEl.textContent = config.deniedLabel || 'Notifications are blocked in your browser settings.';
        }

        if (enableBtn) {
            enableBtn.hidden = true;
        }

        return;
    }

    if (enableBtn) {
        enableBtn.hidden = false;
    }

    if (messageEl && config.promptLabel) {
        messageEl.textContent = config.promptLabel;
    }
}

function wirePushEnableButton(config) {
    const enableBtn = document.getElementById('push-enable-btn');

    if (!enableBtn) {
        return;
    }

    enableBtn.addEventListener('click', async () => {
        enableBtn.disabled = true;

        const result = await initWebPush(config, { requestPermission: true });

        updatePushBanner(config, result);

        if (result.ok) {
            enableBtn.textContent = config.enabledLabel || 'Notifications enabled';
            enableBtn.disabled = true;
        } else {
            enableBtn.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!window.__PUSH_CONFIG__) {
        return;
    }

    const config = window.__PUSH_CONFIG__;

    wirePushEnableButton(config);

    const result = await initWebPush(config, { requestPermission: false });

    updatePushBanner(config, result);
});
