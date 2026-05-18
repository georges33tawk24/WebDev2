@php
    $registerHero = is_file(public_path('images/auth/register-hero.webp'))
        ? asset('images/auth/register-hero.webp')
        : (is_file(public_path('images/auth/register-hero.png'))
            ? asset('images/auth/register-hero.png')
            : asset('images/auth/register-hero.svg'));
@endphp
<div class="hero-copy">
    <div class="hero-img-wrap">
        <img
            src="{{ $registerHero }}"
            alt=""
            class="hero-img"
            width="480"
            height="420"
            loading="lazy"
            decoding="async"
            fetchpriority="high"
        >
    </div>
    <p class="hero-headline">{{ __('ui.auth.hero_register_headline') }}</p>
    <p class="hero-text">{{ __('ui.auth.hero_register_quick') }}</p>
</div>
