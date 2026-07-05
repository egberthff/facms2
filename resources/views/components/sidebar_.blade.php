<aside
    id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen bg-slate-800 border-r border-slate-700">

    <div class="flex flex-col h-full">

        {{-- ========================= --}}
        {{-- Header --}}
        {{-- ========================= --}}

        <div class="px-5 py-6">

            <a href="{{ route('dashboard') }}" class="flex items-center">

                <div class="w-14 h-14 bg-white rounded-lg overflow-hidden">

                    <img
                        src="{{ asset('storage/images/logo/bisu_logo.png') }}"
                        class="w-full h-full object-cover">

                </div>

                <div class="ml-4">

                    <h2 class="text-white text-xl font-bold">
                        FacAssoc
                    </h2>

                    <p class="text-xs uppercase text-slate-300">

                        @role('Member')
                            Member Portal
                        @else
                            Admin Panel
                        @endrole

                    </p>

                </div>

            </a>

        </div>

        <hr class="border-slate-700">

        {{-- ========================= --}}
        {{-- Navigation --}}
        {{-- ========================= --}}

        <div class="flex-1 overflow-y-auto py-4">

            @foreach(config('sidebar') as $section)

                @php

                    $visible = collect($section['items'])->contains(function ($item) {
                        return auth()->user()->hasAnyRole($item['roles']);
                    });

                @endphp

                @if($visible)

                    <div class="mb-6">

                        <h3 class="px-5 mb-3 text-xs uppercase tracking-widest text-slate-400">

                            {{ $section['title'] }}

                        </h3>

                        <ul>

                            @foreach($section['items'] as $item)

                                @if(auth()->user()->hasAnyRole($item['roles']))

                                    <li>

                                        <a
                                            href="{{ route($item['route']) }}"
                                            class="block px-8 py-2 text-center text-white hover:bg-slate-700 transition duration-200
                                            {{ request()->routeIs($item['route']) ? 'bg-slate-700 font-semibold' : '' }}">

                                            {{ $item['label'] }}

                                        </a>

                                    </li>

                                @endif

                            @endforeach

                        </ul>

                    </div>

                @endif

            @endforeach

        </div>

    </div>

</aside>