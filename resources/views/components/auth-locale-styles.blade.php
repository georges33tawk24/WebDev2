<style>
    .auth-locale-bar {
        position: fixed;
        top: 0.5rem;
        right: 0.75rem;
        left: auto;
        z-index: 100;
        pointer-events: none;
    }

    html[dir='rtl'] .auth-locale-bar {
        right: auto;
        left: 0.75rem;
    }

    .auth-locale-bar .locale-toggle {
        pointer-events: auto;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 8px;
        border-radius: 999px;
        text-decoration: none;
        color: #374151;
        cursor: pointer;
        user-select: none;
        background: #e5e7eb;
        border: 1px solid #d1d5db;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .auth-locale-bar .locale-toggle:hover {
        background: #d1d5db;
    }

    .auth-locale-bar .locale-toggle:focus-visible {
        outline: 2px solid #1a56db;
        outline-offset: 2px;
    }

    .auth-locale-bar .locale-toggle__label {
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        opacity: 0.5;
        min-width: 1.35rem;
        text-align: center;
    }

    .auth-locale-bar .locale-toggle--en .locale-toggle__label--en,
    .auth-locale-bar .locale-toggle--ar .locale-toggle__label--ar {
        opacity: 1;
        color: #1a56db;
    }

    .auth-locale-bar .locale-toggle__track {
        position: relative;
        flex-shrink: 0;
        width: 44px;
        height: 24px;
        border-radius: 999px;
        background: #9ca3af;
    }

    .auth-locale-bar .locale-toggle__thumb {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .auth-locale-bar .locale-toggle--ar .locale-toggle__thumb {
        transform: translateX(20px);
    }

    /* Welcome / inline nav (not fixed) */
    header nav .locale-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 8px;
        border-radius: 999px;
        text-decoration: none;
        color: #374151;
        cursor: pointer;
        user-select: none;
        background: #e5e7eb;
        border: 1px solid #d1d5db;
    }

    header nav .locale-toggle__track {
        background: #9ca3af;
    }

    header nav .locale-toggle--en .locale-toggle__label--en,
    header nav .locale-toggle--ar .locale-toggle__label--ar {
        opacity: 1;
        color: #1a56db;
    }
</style>
