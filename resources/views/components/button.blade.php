@props([
    'type'=>'button',
    'variant'=>'primary'
])

@php

$classes = [

'primary'=>'text-white bg-blue-600 hover:bg-blue-700',

'secondary'=>'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50',

'danger'=>'text-white bg-red-600 hover:bg-red-700',

'success'=>'text-white bg-green-600 hover:bg-green-700',

'warning'=>'text-white bg-amber-500 hover:bg-amber-600'

];

@endphp

<button

type="{{ $type }}"

{{ $attributes->merge([

'class'=>'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium shadow-xs transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-60 '.$classes[$variant]

]) }}

>

{{ $slot }}

</button>