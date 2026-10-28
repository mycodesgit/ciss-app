{{-- One menu link: wire:navigate when it has a page, disabled when the user has no access --}}
@props(['item'])

<a href="{{ $item['url'] ?? '#' }}"
    @if ($item['enabled'] && $item['url']) wire:navigate @endif
    {{ $attributes->class(['disabled' => !$item['enabled'], 'active' => $item['active']]) }}
    @if (!$item['enabled']) aria-disabled="true" tabindex="-1" @endif>
    {{ $slot }}
</a>
