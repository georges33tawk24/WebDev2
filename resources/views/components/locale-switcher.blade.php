@php
    $current = app()->getLocale();
    $isAr = $current === 'ar';
    $targetLocale = $isAr ? 'en' : 'ar';
@endphp
<a href="{{ route('locale.switch', $targetLocale) }}"
   class="locale-toggle {{ $isAr ? 'locale-toggle--ar' : 'locale-toggle--en' }}"
   role="switch"
   aria-checked="{{ $isAr ? 'true' : 'false' }}"
   aria-label="{{ __('ui.language') }}: {{ $isAr ? __('ui.lang_ar') : __('ui.lang_en') }}"
   title="{{ $isAr ? __('ui.lang_en') : __('ui.lang_ar') }}">
    <span class="locale-toggle__label locale-toggle__label--en" aria-hidden="true">EN</span>
    <span class="locale-toggle__track" aria-hidden="true">
        <span class="locale-toggle__thumb"></span>
    </span>
    <span class="locale-toggle__label locale-toggle__label--ar" aria-hidden="true">AR</span>
</a>
