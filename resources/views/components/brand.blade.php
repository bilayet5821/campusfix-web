@props(['light' => false])

<a {{ $attributes->class(['brand', 'brand-light' => $light])->merge(['href' => route('home'), 'aria-label' => 'CampusFix home']) }}>
    <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 7.5 12 3l8 4.5L12 12 4 7.5Z"/>
            <path d="M7 10v5.5c2.7 2 7.3 2 10 0V10"/>
            <path d="M20 8v6"/>
        </svg>
    </span>
    <span>CampusFix</span>
</a>
