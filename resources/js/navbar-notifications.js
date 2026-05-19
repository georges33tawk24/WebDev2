import { initNotificationPoll } from './notifications-poll';
import { initLiveRequestTracking } from './live-requests';
import { connectLiveStream } from './live-stream';

if (window.__NOTIFICATIONS_CONFIG__) {
    initNotificationPoll(window.__NOTIFICATIONS_CONFIG__);
}

if (window.__LIVE_CONFIG__?.trackRequests) {
    initLiveRequestTracking();
}

if (window.__LIVE_CONFIG__?.streamUrl && !window.__NOTIFICATIONS_CONFIG__?.streamUrl) {
    connectLiveStream({ streamUrl: window.__LIVE_CONFIG__.streamUrl });
}
