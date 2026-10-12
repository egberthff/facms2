{{-- Notification Dropdown Component --}}
@php
    $unreadCount = auth()->user()->unreadNotifications->count();
@endphp
<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() { this.dropdownOpen = !this.dropdownOpen; },
    closeDropdown() { this.dropdownOpen = false; }
}" @click.away="closeDropdown()">

    <!-- Notification Bell Button -->
    <button class="relative flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-900" @click="toggleDropdown()" type="button" aria-label="Notifications{{ $unreadCount > 0 ? " ({$unreadCount} unread)" : '' }}">

        <!-- Unread Count Badge -->
        @if($unreadCount > 0)
            <span class="absolute -right-1.5 -top-1.5 z-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-orange-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white dark:ring-gray-900">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif

        <!-- SVG Icon -->
        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://w3.org">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z" />
        </svg>
    </button>

    <!-- Dropdown Content Container -->
    <div x-show="dropdownOpen" x-transition class="fixed inset-x-3 top-16 z-50 flex max-h-[70vh] flex-col rounded-xl border border-gray-200 bg-white p-3 shadow-lg ring-1 ring-black/5 sm:absolute sm:inset-x-auto sm:top-auto sm:right-0 sm:mt-3 sm:h-[480px] sm:max-h-none sm:w-[361px] dark:border-gray-800 dark:bg-gray-900" style="display: none;">
        
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
            <h5 class="text-base font-semibold text-gray-900 dark:text-white/90">
                Notifications ({{ $unreadCount }})
            </h5>
        </div>

        <!-- Scrollable Notification Rows -->
        <ul class="flex flex-col h-auto overflow-y-auto custom-scrollbar flex-1">
            @forelse (auth()->user()->notifications()->take(10)->get() as $notification)
                <li>
                    <a class="flex gap-3 rounded-lg p-3 transition-colors hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/5 {{ $notification->unread() ? 'bg-blue-50/40 dark:bg-blue-950/10' : '' }}" href="{{ route('notifications.read', $notification->id) }}">
                        
                        <!-- User Icon Avatar -->
                        <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-gray-800 dark:text-gray-400">
                            <x-heroicon-s-user class="size-5" />
                            @if($notification->unread())
                                <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border border-white bg-blue-500"></span>
                            @endif
                        </span>

                        <!-- Notification Context Copy -->
                        <span class="block">
                            <span class="mb-1 block text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-medium text-gray-800 dark:text-white/90">
                                    {{ $notification->data['user_name'] ?? 'System' }}
                                </span>
                            </span>
                            <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">
                                {{ $notification->data['message'] }}
                            </p>
                            <span class="flex items-center gap-2 mt-1 text-gray-400 text-[11px]">
                                <span class="font-semibold text-blue-500">{{ $notification->data['type'] }}</span>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                            </span>
                        </span>

                    </a>
                </li>
            @empty
                <div class="flex flex-col items-center justify-center p-6 text-center h-full text-gray-400">
                    <p class="text-sm">No claims updates yet.</p>
                </div>
            @endforelse
        </ul>

        @if($unreadCount > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Mark All As Read
                </button>
            </form>
        @endif
    </div>
</div>
