<div class="menu-container border-bottom">
    <div class="container-fluid py-3 d-none d-md-block">
        <div class="menu-grid">
            @foreach ($items as $item)
                <x-employee.menu-link :item="$item" class="menu-item">
                    <i class="{{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </x-employee.menu-link>
            @endforeach
        </div>
    </div>
</div>
