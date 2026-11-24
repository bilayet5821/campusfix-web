@props(['field'])

@error($field)
    <div {{ $attributes->class(['invalid-feedback d-block']) }}>{{ $message }}</div>
@enderror
