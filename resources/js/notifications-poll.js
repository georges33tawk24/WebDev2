import { connectLiveStream } from './live-stream';

export function initNotificationPoll(config) {
    const badge = document.getElementById(config.badgeId || 'navbar-notifications-badge');
    const list = document.getElementById(config.listId || 'navbar-notifications-list');
    const pollUrl = config.pollUrl;
    const markAllUrl = config.markAllUrl;
    const markReadUrlTemplate = config.markReadUrlTemplate;
    const csrf = config.csrf;

    if (!pollUrl) {
        return;
    }

    function renderNotifications(data) {
        const count = Number(data.unread_count || 0);

        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : String(count);
                badge.hidden = false;
            } else {
                badge.hidden = true;
            }
        }

        if (list && Array.isArray(data.notifications)) {
            const emptyLabel = config.emptyLabel || 'No notifications yet.';

            if (data.notifications.length === 0) {
                list.innerHTML =
                    '<li class="navbar-notifications-empty">' + escapeHtml(emptyLabel) + '</li>';
            } else {
                list.innerHTML = data.notifications
                    .map((item) => {
                        const unread = item.read ? '' : ' navbar-notifications-item--unread';
                        const canMark = !item.read && markReadUrlTemplate;
                        const attrs = canMark
                            ? ' role="button" tabindex="0" data-notification-id="' +
                              escapeHtml(String(item.id)) +
                              '" title="' +
                              escapeHtml(config.markOneLabel || 'Mark as read') +
                              '"'
                            : '';
                        return (
                            '<li class="navbar-notifications-item' +
                            unread +
                            (canMark ? ' navbar-notifications-item--action' : '') +
                            '"' +
                            attrs +
                            '>' +
                            '<strong>' +
                            escapeHtml(item.title) +
                            '</strong>' +
                            '<p>' +
                            escapeHtml(item.body) +
                            '</p>' +
                            '<small>' +
                            escapeHtml(item.created_at) +
                            '</small>' +
                            '</li>'
                        );
                    })
                    .join('');
            }
        }
    }

    async function refresh() {
        try {
            const response = await fetch(pollUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            renderNotifications(data);
        } catch {
            // ignore
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    window.addEventListener('live-update', (event) => {
        if (event.detail?.notifications) {
            renderNotifications(event.detail.notifications);
        }
    });

    refresh();

    if (config.streamUrl && typeof EventSource !== 'undefined') {
        connectLiveStream({ streamUrl: config.streamUrl });
    } else {
        const pollMs = Math.max(3000, Number(config.pollSeconds || 5) * 1000);
        window.setInterval(refresh, pollMs);
    }

    async function markOneRead(notificationId) {
        if (!markReadUrlTemplate || !notificationId) {
            return;
        }

        const url = markReadUrlTemplate.replace('__ID__', String(notificationId));

        await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        refresh();
    }

    if (list && markReadUrlTemplate) {
        list.addEventListener('click', (event) => {
            const item = event.target.closest('[data-notification-id]');
            if (!item) {
                return;
            }
            event.preventDefault();
            markOneRead(item.getAttribute('data-notification-id'));
        });

        list.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }
            const item = event.target.closest('[data-notification-id]');
            if (!item) {
                return;
            }
            event.preventDefault();
            markOneRead(item.getAttribute('data-notification-id'));
        });
    }

    const markAllBtn = document.getElementById('navbar-notifications-mark-all');
    if (markAllBtn && markAllUrl) {
        markAllBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            await fetch(markAllUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            refresh();
        });
    }
}
