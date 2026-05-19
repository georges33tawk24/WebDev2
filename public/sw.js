self.addEventListener('push', (event) => {
    let payload = { title: 'E-Services', body: '', url: '/' };

    try {
        if (event.data) {
            payload = { ...payload, ...event.data.json() };
        }
    } catch {
        payload.body = event.data ? event.data.text() : '';
    }

    event.waitUntil(
        self.registration.showNotification(payload.title || 'E-Services', {
            body: payload.body || '',
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: payload.tag || 'eservices',
            data: { url: payload.url || '/' },
        }),
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(url);
            }

            return undefined;
        }),
    );
});
