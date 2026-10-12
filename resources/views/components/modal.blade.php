@props([
    'id',
    'title'
])

<div id="{{ $id }}"
     tabindex="-1"
     aria-hidden="true"
     class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full bg-black/50">

    <div class="relative w-full max-w-2xl p-4">

        <div class="bg-white rounded-xl shadow">

            <div class="flex items-center justify-between p-5 border-b">

                <h3 class="text-lg font-semibold">

                    {{ $title }}

                </h3>

                <button
                    type="button"
                    data-modal-hide="{{ $id }}"
                    class="text-gray-500 hover:text-gray-900">

                    ✕

                </button>

            </div>

            <div class="p-6">

                {{ $slot }}

            </div>

        </div>

    </div>

</div>