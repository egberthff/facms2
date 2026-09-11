<table class="dt-table min-w-full divide-y divide-slate-200">
    <thead class="bg-slate-100 sticky top-0 z-10 text-md-center font-semibold text-slate-700">
        <tr>
            {{-- <th class="w-14 px-5 py-4">
                <input
                    type="checkbox"
                    class="rounded border-slate-300">
            </th> --}}
            <th class="px-5 py-4 text-center text-md font-bold uppercase tracking-wdier text-slate-600">
                Claim
            </th>
            <th class="px-5 py-4 text-center text-md font-bold uppercase tracking-wdier text-slate-600">
                Member
            </th>
            <th class="px-5 py-4 text-center text-md font-bold uppercase tracking-wdier text-slate-600">
                Amount
            </th>
            <th class="px-5 py-4 text-center text-md font-bold uppercase tracking-wider text-slate-600">
                Submitted
            </th>
            <th class="px-5 py-4 text-center text-md font-bold uppercase tracking-wider text-slate-600">
                Status
            </th>
            <th class="px-5 py-4 text-center text-md font-bold uppercase tracking-wider text-slate-600">
                Action
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 bg-white">   
        @forelse($claims as $claim)
        <tr class="hover:bg-blue-50/40 transition-colors text-center duration-150 even:bg-slate-50/30">
            <!-- ============================================== -->
            <!-- SELECT -->
            <!-- ============================================== -->
            {{-- <td class="px-5 py-4 align-top">
                <input
                    type="checkbox"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            </td> --}}
            <!-- ============================================== -->
            <!-- CLAIM INFORMATION -->
            <!-- ============================================== -->
            <td class="px-5 py-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="font-semibold text-slate-900">
                            {{ $claim->claim_number }}
                        </span>
                    </div>
                    <div
                        class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                        {{ $claim->claimType->name }}
                    </div>
                    {{-- <p
                        class="max-w-sm text-sm text-slate-500 leading-relaxed">
                        {{ Str::limit($claim->description ?? $claim->remarks, 70) }}
                    </p> --}}
                </div>
            </td>
            <!-- ============================================== -->
            <!-- MEMBER -->
            <!-- ============================================== -->
            <td class="px-5 py-4">
                <div class="space-y-1">
                    <div class="font-medium text-slate-900">
                        {{ ucwords(strtolower($claim->member->user->name  ?? 'Unknown Member')) }}
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ $claim->member->campus->acronym ?? 'No Campus' }} | EMP ID: {{ $claim->member->employee_number ?? '' }}
                    </div>
                </div>
            </td>
            <!-- ============================================== -->
            <!-- AMOUNT -->
            <!-- ============================================== -->
            <td class="px-5 py-4 text-right">
                <div class="text-lg font-bold text-emerald-600">
                    ₱{{ number_format($claim->requested_amount,2) }}
                </div>
                <div class="text-xs text-slate-500">
                    Requested
                </div>
            </td>
            <!-- ============================================== -->
            <!-- SUBMITTED -->
            <!-- ============================================== -->
            <td class="px-5 py-4 text-center">
                <div class="font-medium text-slate-800">
                    {{ $claim->submitted_at->format('M d, Y') }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    {{ $claim->submitted_at->format('h:i A') }}
                </div>
            </td>
            <!-- ============================================== -->
            <!-- STATUS -->
            <!-- ============================================== -->
            <td class="px-5 py-4 text-center">
                @php
                    $statusClasses = match($claim->status) {
                        'Pending'    => 'bg-amber-100 text-amber-800 ring-amber-200',
                        'Processing' => 'bg-blue-100 text-blue-800 ring-blue-200',
                        'Approved'   => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                        'Rejected'   => 'bg-rose-100 text-rose-800 ring-rose-200',
                        'Disbursed'  => 'bg-purple-100 text-purple-800 ring-purple-200',
                        'For Disbursement' => 'bg-orange-100 text-orange-800 ring-orange-200',
                        'For Review FP' => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                        default      => 'bg-slate-100 text-slate-700 ring-slate-200',
                    };
                @endphp
                <span
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                    <span class="h-2 w-2 rounded-full bg-current opacity-75"></span>
                    {{ $claim->status }}
                </span>
            </td>
            <!-- ============================================== -->
            <!-- ACTION -->
            <!-- ============================================== -->
            <td class="px-5 py-4 text-center">
                <div
                    x-data="{ open:false }"
                    class="relative inline-block text-left">
                    @if(isset($isMemberMenu) && $isMemberMenu)
                        <button
                            @click="
                                activeClaim = @js($claim);
                                openModal = true;
                                open = false;
                                "
                                class="flex w-full items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                                <x-heroicon-o-eye class="size-6 text-blue-600" />
                                <span class="ml-3">View Details</span>
                            </button>
                        @else
                    <button
                        @click="open=!open"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-100 transition">
                        Actions
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    @endif
                     
                    <div
                        x-show="open"
                        @click.away="open=false"
                        x-transition
                        class="absolute right-0 mt-2 w-52 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-slate-200 z-50">
                        <div class="py-2">
                            <button
                                @click="
                                    activeClaim = @js($claim);
                                    openModal = true;
                                    open = false;
                                "
                                class="flex w-full items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                                <x-heroicon-o-eye class="size-6 text-blue-600" />
                                <span class="ml-3">View Details</span>
                            </button>
                            @if($userRole === 'President' && strtolower(trim($claim->status ?? '')) === 'pending')
                                    <button
                                        @click="updateClaimStatus('{{ $claim->id }}', 'Approved', @js($claim))"
                                        class="flex w-full items-center px-4 py-2.5 text-sm text-blue-700 hover:bg-blue-50">
                                        <x-heroicon-o-check class="size-5 text-blue-500" />
                                        <span class="ml-3">Approve</span>
                                    </button>
                                    <button
                                        @click="updateClaimStatus('{{ $claim->id }}', 'Rejected', @js($claim))"
                                        class="flex w-full items-center px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50">
                                        <x-heroicon-o-x-mark class="size-5 text-rose-500" />
                                        <span class="ml-3">Reject</span>
                                    </button>
                            @elseif($userRole === 'Treasurer')
                                @if(strtolower(trim($claim->status ?? '')) === 'approved')
                                <button
                                    @click="updateClaimStatus('{{ $claim->id }}', 'For Review FP', @js($claim))"
                                    class="flex w-full items-center px-4 py-2.5 text-sm text-blue-700 hover:bg-blue-50">
                                    <x-heroicon-o-arrow-path class="size-5 text-blue-500" />
                                    <span class="ml-3">Forward to FP</span>
                                </button>
                                @elseif(strtolower(trim($claim->status ?? '')) === 'for disbursement')
                                <button
                                    @click="updateClaimStatus('{{ $claim->id }}', 'Disbursed', @js($claim))"
                                    class="flex w-full items-center px-4 py-2.5 text-sm text-emerald-700 hover:bg-emerald-50">
                                    <x-heroicon-o-credit-card class="size-5 text-emerald-500" />
                                    <span class="ml-3">Mark as Disbursed</span>
                                </button>
                                @endif
                            @elseif($userRole === 'Federated President' && strtolower(trim($claim->status ?? '')) === 'for review fp')
                                <button
                                    @click="updateClaimStatus('{{ $claim->id }}', 'Processing', @js($claim))"
                                    class="flex w-full items-center px-4 py-2.5 text-sm text-amber-700 hover:bg-amber-50">
                                    <x-heroicon-o-arrow-path class="size-5 text-amber-500" />
                                    <span class="ml-3">Processing</span>
                                </button>
                            @elseif($userRole === 'Federated Treasurer' && strtolower(trim($claim->status ?? '')) === 'processing')
                                <button
                                    @click="updateClaimStatus('{{ $claim->id }}', 'For Disbursement', @js($claim))"
                                    class="flex w-full items-center px-4 py-2.5 text-sm text-emerald-700 hover:bg-emerald-50">
                                    <x-heroicon-o-credit-card class="size-5 text-emerald-500" />
                                    <span class="ml-3">For Disbursement</span>
                                </button>
                            @endif
                            
                            {{-- <button class="flex w-full items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                                <x-heroicon-o-clock class="size-6 text-amber-500" />
                                <span class="ml-3">View Timeline</span>
                            </button> --}}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-6 py-20">
                <div class="text-center">
                    <svg
                        class="mx-auto h-14 w-14 text-slate-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                    </svg>
                    <h3 class="mt-5 text-lg font-semibold text-slate-800">
                        No Claims Found
                    </h3>
                    <p class="mt-2 text-sm text-slate-500">
                        No claims match the selected filters.
                    </p>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>