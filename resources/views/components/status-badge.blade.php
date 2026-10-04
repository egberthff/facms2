@props([
    'status'
])

@php

$colors = [

'Active'=>'bg-green-50 text-green-700 ring-green-600/20',

'Inactive'=>'bg-gray-50 text-gray-600 ring-gray-500/20',

'Pending'=>'bg-amber-50 text-amber-700 ring-amber-600/20',

'Approved'=>'bg-green-50 text-green-700 ring-green-600/20',

'Rejected'=>'bg-red-50 text-red-700 ring-red-600/20',

'Paid'=>'bg-blue-50 text-blue-700 ring-blue-600/20',

];

@endphp

<span
class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $colors[$status] ?? 'bg-gray-50 text-gray-600 ring-gray-500/20' }}">

{{ $status }}

</span>