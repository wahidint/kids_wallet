{{--
    Reusable form field with label, validation error and optional help text.
    Usage:  <x-form-input name="email" label="Email address" type="email" required />
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-medium">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
           {{ $attributes->class(['form-control form-control-lg', 'is-invalid' => $errors->has($name)]) }}>
    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>