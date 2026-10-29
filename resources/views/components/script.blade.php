{{--
    Usage: x-script with src="js/employee/crud.js" (a path under public/). Adds a version (file modified time):
    - ?v= makes the browser download the file again whenever it changes (no stale cached JS).
    - data-navigate-track makes wire:navigate do one full page reload when the version changed, so the
      old copy that wire:navigate keeps loaded in <head> is replaced too.
--}}
@props(['src'])

<script src="{{ asset($src) }}?v={{ @filemtime(public_path($src)) }}" data-navigate-track></script>
