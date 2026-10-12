@props([
    'type'=>'button',
    'variant'=>'primary'
])

@php

$classes = [

'primary'=>'text-white bg-blue-600 hover:bg-blue-700',

'secondary'=>'text-gray-700 bg-gray-200 hover:bg-gray-300',

'danger'=>'text-white bg-red-600 hover:bg-red-700',

'success'=>'text-white bg-green-600 hover:bg-green-700',

'warning'=>'text-white bg-yellow-500 hover:bg-yellow-600'

];

@endphp

<button

type="{{ $type }}"

{{ $attributes->merge([

'class'=>'px-5 py-2.5 rounded-lg font-medium transition '.$classes[$variant]

]) }}

>

{{ $slot }}

</button>