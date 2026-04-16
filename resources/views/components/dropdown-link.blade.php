@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full px-4 py-2 text-start text-sm leading-5 text-black hover:bg-[#cfae86]'
    : 'block w-full px-4 py-2 text-start text-sm leading-5 text-black hover:bg-[#cfae86]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
