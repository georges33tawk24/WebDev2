@php
    $loginHero = is_file(public_path('images/auth/login-hero.webp'))
        ? asset('images/auth/login-hero.webp')
        : (is_file(public_path('images/auth/login-hero.png'))
            ? asset('images/auth/login-hero.png')
            : asset('images/auth/login-hero.svg'));
@endphp
<div class="hero-copy">
    <div class="hero-img-wrap">
        <img
            src="{{ $loginHero }}"
            alt=""
            class="hero-img"
            width="480"
            height="420"
            loading="lazy"
            decoding="async"
            fetchpriority="high"
        >
    </div>
    <p class="hero-headline">{{ __('ui.auth.hero_security_title') }}</p>
    <p class="hero-text">{{ __('ui.auth.hero_security_text') }}</p>
</div>
