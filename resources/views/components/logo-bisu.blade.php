{{-- <img src="{{ asset('storage/images/logo/bisu_logo.png') }}" alt="Bohol Island State University Logo" class="object-contain max-h-full"> --}}

<img 
    src="{{ asset('storage/images/logo/bisu_logo.png') }}" 
    alt="Bohol Island State University Logo" 
    {{ $attributes->merge(['class' => 'object-contain']) }}
>
