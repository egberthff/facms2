@props([
    'status'
])

@php

$colors = [

'Active'=>'bg-green-100 text-green-800',

'Inactive'=>'bg-gray-100 text-gray-800',

'Pending'=>'bg-yellow-100 text-yellow-800',

'Approved'=>'bg-green-100 text-green-800',

'Rejected'=>'bg-red-100 text-red-800',

'Paid'=>'bg-blue-100 text-blue-800',

];

@endphp

<span
class="px-3 py-1 rounded-full text-xs font-medium {{ $colors[$status] ?? 'bg-gray-100 text-gray-700' }}">

{{ $status }}

</span>