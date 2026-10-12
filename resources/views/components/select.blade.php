@props(['label' => null, 'name' => '', 'options' => []])
<select name="{{ $name }}" {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20']) }}>
    @foreach($options as $value => $text)
        <option value="{{ $value }}">{{ $text }}</option>
    @endforeach
</select>