@props(['value', 'for' => ''])

@php
    // Prefer an explicit prop, otherwise use any 'for' passed via attributes.
    $forAttr = $for ?: $attributes->get('for');
@endphp

<label for="{{ $forAttr }}" {{ $attributes->except('for')->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
