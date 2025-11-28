@props([
    'name' => null,
    'class' => 'h-5 w-5',
])

@php
    $classes = $attributes->merge(['class' => $class])->get('class');
@endphp

@switch($name)
    @case('chart-bar')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M3 20h18" />
            <path d="M7 16v-6" />
            <path d="M12 16V8" />
            <path d="M17 16v-4" />
        </svg>
    @break

    @case('rectangle-stack')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <rect x="6" y="6" width="12" height="10" rx="2" />
            <path d="M8 4h8a2 2 0 0 1 2 2" />
        </svg>
    @break

    @case('book-open')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M12 5c-2.5-1.5-5-1.5-8 0v12c3-1.5 5.5-1.5 8 0" />
            <path d="M12 5c2.5-1.5 5-1.5 8 0v12c-3-1.5-5.5-1.5-8 0" />
        </svg>
    @break

    @case('bookmark')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M7 4h10a1 1 0 0 1 1 1v15l-6-3-6 3V5a1 1 0 0 1 1-1z" />
        </svg>
    @break

    @case('exclamation-triangle')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M12 3l10 17H2L12 3z" />
            <path d="M12 9v4" />
            <path d="M12 17h.01" />
        </svg>
    @break

    @case('arrow-down-tray')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M12 3v10" />
            <path d="M8 9l4 4 4-4" />
            <path d="M4 21h16" />
        </svg>
    @break

    @case('document-text')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" />
            <path d="M14 3v5h5" />
            <path d="M9 12h6" />
            <path d="M9 16h6" />
        </svg>
    @break

    @default
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <circle cx="12" cy="12" r="9" />
        </svg>
@endswitch