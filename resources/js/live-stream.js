/**
 * Server-Sent Events live channel — reconnects every ~30s (server closes stream).
 */
export function connectLiveStream(config) {
    const streamUrl = config?.streamUrl;

    if (!streamUrl || typeof EventSource === 'undefined') {
        return null;
    }

    let source = null;
    let cursor = Number(config.initialCursor || 0);

    function connect() {
        const url = new URL(streamUrl, window.location.origin);
        if (cursor > 0) {
            url.searchParams.set('cursor', String(cursor));
        }

        source = new EventSource(url.toString());

        source.addEventListener('update', (event) => {
            try {
                const data = JSON.parse(event.data);
                if (data.cursor) {
                    cursor = Number(data.cursor);
                }
                window.dispatchEvent(new CustomEvent('live-update', { detail: data }));
            } catch {
                // ignore malformed payloads
            }
        });

        source.onerror = () => {
            source?.close();
            window.setTimeout(connect, 3000);
        };
    }

    connect();

    return () => source?.close();
}
