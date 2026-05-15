<x-layouts.auth-flow title="Protected — WebDev2">
    <div class="success-mark-wrap" aria-hidden="true">
        <span class="success-ring"></span>
        <span class="success-dot success-dot--1"></span>
        <span class="success-dot success-dot--2"></span>
        <span class="success-dot success-dot--3"></span>
        <span class="success-check">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="11" fill="#22c55e"/>
                <path d="M7 12l3 3 7-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    </div>

    <h1 class="twofa-title twofa-title--success">Your account is now protected!</h1>
    <p class="twofa-sub">You can access your dashboard with two-factor verification enabled.</p>

    <a href="{{ route($continueRoute) }}" class="btn-primary btn-block success-cta">Continue</a>
</x-layouts.auth-flow>
