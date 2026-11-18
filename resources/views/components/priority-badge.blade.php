@props(['priority'])

@php $value = in_array($priority, ['low', 'medium', 'high', 'urgent'], true) ? $priority : 'medium'; @endphp

<span {{ $attributes->class(['priority-badge', 'priority-'.$value]) }}>{{ ucfirst($value) }}</span>
