<x-app-layout>

<div class="max-w-7xl mx-auto p-4">
    <!-- ========================================= -->
    <!-- CLAIMS LIST CARD -->
    <!-- ========================================= -->
    <div class="bg-white border border-blue-700 rounded-2xl shadow-sm">
        <!-- Header -->
        <div class="p-4 border-b">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Filters -->
                <div class="flex flex-wrap gap-2">
                    <button class="px-4 py-2 text-sm rounded-lg border bg-blue-600 text-white">
                        All
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-100">
                        Pending
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-100">
                        Processing
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-100">
                        Approved
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-100">
                        Disbursed
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-100">
                        Rejected
                    </button>
                </div>
                <!-- Search -->
                <div class="relative w-full lg:w-72">
                    <input
                        type="text"
                        placeholder="Search claim..."
                        class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-orange-400">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">
                            Ref #
                        </th>
                        <th class="px-4 py-3 text-left font-semibold">
                            Claim Type
                        </th>
                        <th class="px-4 py-3 text-left font-semibold">
                            Description
                        </th>
                        <th class="px-4 py-3 text-right font-semibold">
                            Amount
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            Date Filed
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            Status
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($claims as $claim)
                    <tr>
                        <td>{{ $claim->claim_number }}</td>
                        <td>{{ $claim->claimType->name }}</td>
                        <td>{{ Str::limit($claim->remarks,40) }}</td>
                        <td>{{ number_format($claim->requested_amount,2) }}</td>
                        <td>{{ $claim->submitted_at->format('M d, Y') }}</td>
                        <td>
                            @switch($claim->status)
                                @case('Pending')
                                    <span class="badge badge-warning">
                                        Pending
                                    </span>
                                @break
                                @case('Approved')
                                    ...
                            @endswitch
                        </td>
                        <td>
                            ...
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(function () {

        $('#claims-table').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            responsive: true,
            lengthChange: false,
            dom: 'rtip',
            language: {
                search: "",
                searchPlaceholder: "Search claims..."
            }
        });

    });

    $('.status-filter').click(function(){
        let status = $(this).data('status');
        if(status == 'all'){
            table.column(5).search('').draw();
        }
        else{
            table.column(5).search(status).draw();
        }
    });

    $('#search').keyup(function(){
    table.search($(this).val()).draw();
    });
</script>

</x-app-layout>