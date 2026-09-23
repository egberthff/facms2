@props([
    'campusName' => 'Balilihan Campus',
    'address' => 'Magsija, Balilihan 6342, Bohol, Philippines'
])


<header class="report-header">
    <div class="header-logo-left">
        <x-logo-bisu class="header-logo-left object-contain" />
    </div>

    <div class="header-text">
        <p class="agency">Republic of the Philippines</p>
        <h2>BOHOL ISLAND STATE UNIVERSITY</h2>
        <p class="address">{{ $address}}</p>
        <p class="association font-bold text-gray-900">
            Faculty Association - {{ $campusName }}
        </p>
        <p class="motto">Balance | Integrity | Stewardship | Uprightness</p>
    </div>

    <div class="header-logo-right">
        <x-logo-bp-iso class="header-logo-right object-contain" />
    </div>
</header>
