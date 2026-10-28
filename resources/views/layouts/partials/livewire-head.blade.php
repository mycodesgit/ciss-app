{{--
    Livewire builds its update URL as "/livewire/update" (relative to the server root),
    which misses the app's subfolder (e.g. /ciss-app/public). Livewire's JS reads the
    first [data-update-uri] on the page, so this full URL takes priority.
--}}
<meta name="livewire-update-uri" data-update-uri="{{ url('livewire/update') }}">

@livewireStyles
