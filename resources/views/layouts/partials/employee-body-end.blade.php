{{-- End of <body> for employee layouts. Body scripts re-run on every wire:navigate visit. --}}
<script>
    @if (Session::has('error'))
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right'
        };
        toastr.error(@json(session('error')));
    @endif

    document.querySelectorAll('.card-animate').forEach((card, index) => {
        setTimeout(() => card.classList.add('show'), index * 100); // stagger effect
    });
</script>

@livewireScripts
