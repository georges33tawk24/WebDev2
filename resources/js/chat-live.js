/**
 * Live chat polling + AJAX send for citizen/staff chat pages.
 */
export function initLiveChat(config) {
    const thread = document.getElementById(config.threadId || 'chat-thread');
    const form = document.getElementById(config.formId || 'chat-form');
    const input = document.getElementById(config.inputId || 'chat-message-input');
    const errorEl = document.getElementById(config.errorId || 'chat-form-error');
    const emptyEl = document.getElementById(config.emptyId || 'chat-thread-empty');
    const pollUrl = config.pollUrl;
    const sendUrl = config.sendUrl;
    const csrf = config.csrf;
    const currentUserId = Number(config.currentUserId);
    let lastId = Number(config.lastMessageId || 0);
    let polling = null;

    if (!thread || !pollUrl) {
        return;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderMessage(msg) {
        const row = document.createElement('div');
        row.className = 'chat-bubble-row ' + (msg.is_mine ? 'chat-bubble-row--mine' : 'chat-bubble-row--theirs');
        row.dataset.messageId = String(msg.id);

        row.innerHTML =
            '<div class="chat-bubble ' + (msg.is_mine ? 'chat-bubble--mine' : 'chat-bubble--theirs') + '">' +
            '<p class="chat-bubble__author">' + escapeHtml(msg.sender_name) + '</p>' +
            '<p class="chat-bubble__text">' + escapeHtml(msg.message) + '</p>' +
            '<p class="chat-bubble__time">' + escapeHtml(msg.created_at) + '</p>' +
            '</div>';

        return row;
    }

    function appendMessages(messages) {
        if (!messages.length) {
            return;
        }

        if (emptyEl) {
            emptyEl.remove();
        }

        messages.forEach((msg) => {
            if (thread.querySelector('[data-message-id="' + msg.id + '"]')) {
                return;
            }
            thread.appendChild(renderMessage(msg));
            lastId = Math.max(lastId, Number(msg.id));
        });

        thread.scrollTop = thread.scrollHeight;
    }

    async function poll() {
        try {
            const url = new URL(pollUrl, window.location.origin);
            if (lastId > 0) {
                url.searchParams.set('after_id', String(lastId));
            }
            url.searchParams.set('mark_read', '1');

            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            appendMessages(data.messages || []);
        } catch {
            // ignore transient network errors
        }
    }

    async function sendMessage(text) {
        const response = await fetch(sendUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: text }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const err = data.errors?.message?.[0] || data.message || 'Failed to send';
            throw new Error(err);
        }

        if (data.message) {
            data.message.is_mine = Number(data.message.sender_id) === currentUserId;
            appendMessages([data.message]);
        }
    }

    if (form && input && sendUrl) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const text = input.value.trim();
            if (!text) {
                return;
            }

            if (errorEl) {
                errorEl.textContent = '';
                errorEl.hidden = true;
            }

            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            try {
                await sendMessage(text);
                input.value = '';
            } catch (err) {
                if (errorEl) {
                    errorEl.textContent = err.message;
                    errorEl.hidden = false;
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                input.focus();
            }
        });
    }

    poll();

    window.addEventListener('live-update', () => {
        poll();
    });

    polling = window.setInterval(poll, 5000);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (polling) {
                window.clearInterval(polling);
                polling = null;
            }
        } else if (!polling) {
            poll();
            polling = window.setInterval(poll, 2500);
        }
    });
}
