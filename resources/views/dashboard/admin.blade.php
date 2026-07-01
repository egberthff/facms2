<x-app-layout>

<x-page-header
title="Dashboard"
description="Faculty Association Claims Monitoring System"/>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
<x-stats-card
title="Members"
:value="$members">

<x-slot:icon>

<x-heroicon-o-users
class="w-10 h-10 text-blue-600"/>

</x-slot:icon>

</x-stats-card>

<x-stats-card
title="Total Claims"
{{-- :value="$totalClaims" --}}
:value="10">

<x-slot:icon> 

<x-heroicon-o-currency-dollar
class="w-10 h-10 text-blue-600"/>

</x-slot:icon>

</x-stats-card>

<x-stats-card
title="Processing Claims"
{{-- :value="$processingClaims" --}}
:value="13">

<x-slot:icon>

<x-heroicon-o-clock
class="w-10 h-10 text-yellow-500"/>

</x-slot:icon>

</x-stats-card>

<x-stats-card
title="Pending Claims"
:value="$pendingClaims">

<x-slot:icon>

<x-heroicon-o-clock
class="w-10 h-10 text-yellow-500"/>

</x-slot:icon>

</x-stats-card>

<x-stats-card
title="Approved"
:value="$approvedClaims">

<x-slot:icon>

<x-heroicon-o-check-circle
class="w-10 h-10 text-green-600"/>

</x-slot:icon>

</x-stats-card>

<x-stats-card
title="Disbursed Claims"
{{-- :value="$disbursedClaims" --}}
:value="0">

<x-slot:icon>

<x-heroicon-o-clock
class="w-10 h-10 text-yellow-500"/>

</x-slot:icon>

</x-stats-card>

<x-stats-card
title="Rejected"
:value="$rejectedClaims">

<x-slot:icon>

<x-heroicon-o-x-circle
class="w-10 h-10 text-red-600"/>

</x-slot:icon>

</x-stats-card>

</div>

</x-app-layout>