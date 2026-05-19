import { initLiveChat } from './chat-live';

if (window.__CHAT_CONFIG__) {
    initLiveChat(window.__CHAT_CONFIG__);
}
