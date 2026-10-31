{{--
    Livewire builds its update URL as "/livewire/update" (relative to the server root),
    which misses the app's subfolder (e.g. /ciss-app/public). Livewire's JS reads the
    first [data-update-uri] on the page, so this full URL takes priority.
--}}
<meta name="livewire-update-uri" data-update-uri="{{ url('livewire/update') }}">

@livewireStyles

{{-- wire:navigate progress bar: Livewire puts it at z-index 1031, under the fixed topbar and logo. The browser maximum keeps it above everything. --}}
<style>
    #nprogress,
    #nprogress .bar,
    #nprogress .spinner {
        z-index: 2147483647 !important;
    }
</style>
