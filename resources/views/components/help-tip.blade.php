@props(['text', 'label' => 'Buka petunjuk'])
@php($helpId = 'help-'.Illuminate\Support\Str::uuid())
<span class="help-tip" data-help-tip>
    <button type="button" class="help-tip-trigger" aria-label="{{ $label }}" aria-controls="{{ $helpId }}" aria-expanded="false">?</button>
    <span id="{{ $helpId }}" class="help-tip-bubble" role="tooltip" hidden>{{ $text }}</span>
</span>
