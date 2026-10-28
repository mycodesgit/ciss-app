@php
    // Mobile uses shorter labels/icons for some items
    $bar = [
        'home-url' => ['label' => 'Dashboard', 'icon' => 'fas fa-th'],
        'enrollment-url' => ['label' => 'Enrollment', 'icon' => 'fas fa-graduation-cap'],
    ];
    $others = [
        'admission-url' => ['label' => 'Admission', 'icon' => 'fas fa-id-card'],
        'scheduler-url' => ['label' => 'Schedule', 'icon' => 'fas fa-calendar-alt'],
        'assessment-url' => ['label' => 'Assess', 'icon' => 'fas fa-receipt'],
        'cashiering-url' => ['label' => 'Cashier', 'icon' => 'fas fa-calculator'],
        'kiosk-url' => ['label' => 'Kiosk', 'icon' => 'fas fa-laptop'],
    ];
@endphp

<div class="bottom-nav">
    @foreach ($bar as $key => $look)
        <x-employee.menu-link :item="$items[$key]">
            <div class="nav-item" data-label="{{ $look['label'] }}">
                <i class="{{ $look['icon'] }} icon"></i>
                <span>{{ $look['label'] }}</span>
            </div>
        </x-employee.menu-link>
    @endforeach

    <a href="#" onclick="event.preventDefault(); document.getElementById('servicesMenu').classList.toggle('active')">
        <div class="nav-item" data-label="Others">
            <i class="fas fa-server icon"></i>
            <span>Others</span>
        </div>
    </a>

    <x-employee.menu-link :item="$items['setting-url']">
        <div class="nav-item" data-label="Settings">
            <i class="fas fa-cog icon"></i>
            <span>Settings</span>
        </div>
    </x-employee.menu-link>

    <a href="{{ route('employee.logout') }}" onclick="event.preventDefault(); document.getElementById('employee-logout-form').requestSubmit()">
        <div class="nav-item" data-label="Sign Out">
            <i class="fas fa-sign-out icon"></i>
            <span>Sign Out</span>
        </div>
    </a>
</div>

<div class="services-menu" id="servicesMenu">
    @foreach ($others as $key => $look)
        <x-employee.menu-link :item="$items[$key]">
            <div class="service-item">
                <i class="{{ $look['icon'] }}"></i>
                <span>{{ $look['label'] }}</span>
            </div>
        </x-employee.menu-link>
    @endforeach
</div>
