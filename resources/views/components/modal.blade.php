@props([
    'id',
    'title'
])

<div id="{{ $id }}"
     tabindex="-1"
     aria-hidden="true"
     class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full bg-slate-900/50 p-4">

    <div class="relative w-full max-w-2xl max-h-full">

        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-black/5">

            <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-4 py-4 sm:px-6">

                <h3 class="text-base font-semibold text-gray-900 sm:text-lg">

                    {{ $title }}

                </h3>

                <button
                    type="button"
                    data-modal-hide="{{ $id }}"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-900">

                    ✕

                </button>

            </div>

            <div class="overflow-y-auto p-4 sm:p-6">

                {{ $slot }}

            </div>

        </div>

    </div>

</div>