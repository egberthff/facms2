<!-- <template x-if="hasGenerated">
        <div
            x-transition
            class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden print">
            {{-- Header --}}
            <div
                class="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2
                        class="font-semibold text-gray-900"
                        x-text="context.title">
                    </h2>
                    <p
                        class="mt-1 text-sm text-gray-500"
                        x-text="context.description">
                    </p>
                </div>
                <div
                    class="text-sm text-gray-500">
                    Total Records :
                    <span class="font-semibold text-gray-800" 
                          x-text="cards.find(c => c.key === 'claims')?.value ?? 0">
                    </span> 
                </div>
            </div>
            {{-- Table --}}
            <div
                class="overflow-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead
                        class="bg-gray-50">
                        <tr>
                            <template
                                x-for="column in columns"
                                :key="column.key">
                                <th
                                    class="px-6 py-3 text-left uppercase tracking-wider text-xs font-semibold text-gray-600 whitespace-nowrap"
                                    x-text="column.label">
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100">
                        <template
                            x-for="(row,index) in rows"
                            :key="index">
                            <tr
                                class="hover:bg-gray-50 transition">
                                <template
                                    x-for="column in columns"
                                    :key="column.key">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap">
                                        {{-- Badge --}}
                                        <template
                                            x-if="column.type=='badge'">
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                                :class="badgeClass(row[column.key])"
                                                x-text="row[column.key]">
                                            </span>
                                        </template>

                                        {{-- Currency --}}

                                        <template
                                            x-if="column.type=='currency'">
                                            <span
                                                class="font-semibold text-gray-800"
                                                x-text="formatCurrency(row[column.key])">
                                            </span>
                                        </template>

                                        {{-- Number --}}

                                        <template
                                            x-if="column.type=='number'">
                                            <span
                                                class="text-gray-700"
                                                x-text="formatNumber(row[column.key])">
                                            </span>
                                        </template>

                                        {{-- Date --}}

                                        <template
                                            x-if="column.type=='date'">
                                            <span
                                                class="text-gray-700"
                                                x-text="formatDate(row[column.key])">
                                            </span>
                                        </template>

                                        {{-- Default Text --}}

                                        <template
                                            x-if="!column.type || column.type=='text'">
                                            <span
                                                class="text-gray-700"
                                                x-text="row[column.key]">
                                            </span>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}

            <div
                class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                <div
                    class="text-sm text-gray-500">
                    Generated using Faculty Association Claims Monitoring System
                </div>
                <div
                    class="text-sm text-gray-500">
                    <span class="font-semibold text-gray-800" 
                          x-text="cards.find(c => c.key === 'claims')?.value ?? 0">
                    </span> 
                    records
                </div>
            </div>
        </div>
    </template> -->

    <!-- Working version -->
  <!--  <template x-if="hasGenerated">
        <div
            x-transition
            class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden print">
            
            {{-- Header --}}
            <div
                class="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2
                        class="font-semibold text-gray-900"
                        x-text="context.title">
                    </h2>
                    <p
                        class="mt-1 text-sm text-gray-500"
                        x-text="context.description">
                    </p>
                </div>
                
                {{-- Dynamic Stat Pill: Handles both Claims count and Collections currency dynamically --}}
                <div class="text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                    <span x-text="cards[0]?.label + ': '"></span>
                    <span class="font-bold text-gray-900" 
                          x-text="cards[0]?.key === 'collections' ? formatCurrency(cards[0]?.value) : formatNumber(cards[0]?.value ?? 0)">
                    </span> 
                </div>
            </div>
    
            {{-- Table --}}
            <div class="overflow-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <template x-for="column in columns" :key="column.key">
                                <th
                                    class="px-6 py-3 text-left uppercase tracking-wider text-xs font-semibold text-gray-600 whitespace-nowrap"
                                    x-text="column.label">
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        {{-- Empty State handling since collectionSummary rows can be empty --}}
                        <template x-if="rows.length === 0">
                            <tr>
                                <td :colspan="columns.length" class="px-6 py-10 text-center text-gray-400 italic">
                                    No records found matching the criteria.
                                </td>
                            </tr>
                        </template>
    
                        <template x-for="(row, index) in rows" :key="index">
                            <tr class="hover:bg-gray-50 transition">
                                <template x-for="column in columns" :key="column.key">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- Badge --}}
                                        <template x-if="column.type == 'badge'">
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                                :class="badgeClass(row[column.key])"
                                                x-text="row[column.key]">
                                            </span>
                                        </template>
    
                                        {{-- Currency --}}
                                        <template x-if="column.type == 'currency'">
                                            <span class="font-semibold text-gray-800"
                                                  x-text="formatCurrency(row[column.key])">
                                            </span>
                                        </template>
    
                                        {{-- Number Fix: Now explicitly handled so numbers don't show up blank --}}
                                        <template x-if="column.type == 'number'">
                                            <span class="text-gray-700"
                                                  x-text="formatNumber(row[column.key])">
                                            </span>
                                        </template>
    
                                        {{-- Date --}}
                                        <template x-if="column.type == 'date'">
                                            <span class="text-gray-700"
                                                  x-text="formatDate(row[column.key])">
                                            </span>
                                        </template>
    
                                        {{-- Default Text --}}
                                        <template x-if="!column.type || column.type == 'text'">
                                            <span class="text-gray-700"
                                                  x-text="row[column.key]">
                                            </span>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
    
            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-2">
                <div class="text-xs text-gray-400">
                    Generated via Faculty Association Claims Monitoring System
                </div>
                
                {{-- Context-aware summary subtitle fallback --}}
                <div class="text-sm text-gray-500 italic">
                    <span x-text="context.title === 'Collection Summary' ? (summary?.period_label ?? 'All approved transfers') : (rows.length + ' monthly data tracks')"></span>
                </div>
            </div>
        </div>
    </template> -->

    <!--Testing version-->
    <template x-if="hasGenerated">
    <div
        x-transition
        class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden print">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <div
            class="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>
                <h2
                    class="font-semibold text-gray-900"
                    x-text="context.title">
                </h2>

                <p
                    class="mt-1 text-sm text-gray-500"
                    x-text="context.description">
                </p>
            </div>

            <div
                class="text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">

                <span x-text="rows.length"></span>
                <span> record(s)</span>

            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- TABLE --}}
        {{-- ========================================================= --}}
        <div class="w-full overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200 text-sm">

                <thead class="bg-gray-50">

                    <tr>

                        <template
                            x-for="column in columns"
                            :key="column.key">

                            <th
                                class="px-6 py-3 text-left uppercase tracking-wider text-xs font-semibold text-gray-600 whitespace-nowrap"
                                x-text="column.label">
                            </th>

                        </template>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100">

                    {{-- Empty State --}}
                    <template x-if="rows.length === 0">

                        <tr>
                            <td
                                :colspan="columns.length"
                                class="px-6 py-10 text-center text-gray-400 italic">

                                No records found matching the criteria.

                            </td>
                        </tr>

                    </template>


                    {{-- Rows --}}
                    <template
                        x-for="(row, index) in rows"
                        :key="index">

                        <tr class="hover:bg-gray-50 transition">

                            <template
                                x-for="column in columns"
                                :key="column.key">

                                <td class="px-6 py-4 whitespace-nowrap">

                                    {{-- Badge --}}
                                    <template x-if="column.type === 'badge'">

                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                            :class="badgeClass(row[column.key])"
                                            x-text="row[column.key]">
                                        </span>

                                    </template>


                                    {{-- Currency --}}
                                    <template x-if="column.type === 'currency'">

                                        <span
                                            class="font-semibold text-gray-800"
                                            x-text="formatCurrency(row[column.key])">
                                        </span>

                                    </template>


                                    {{-- Number --}}
                                    <template x-if="column.type === 'number'">

                                        <span
                                            class="text-gray-700"
                                            x-text="formatNumber(row[column.key])">
                                        </span>

                                    </template>


                                    {{-- Date --}}
                                    <template x-if="column.type === 'date'">

                                        <span
                                            class="text-gray-700"
                                            x-text="formatDate(row[column.key])">
                                        </span>

                                    </template>


                                    {{-- Text --}}
                                    <template
                                        x-if="!column.type || column.type === 'text'">

                                        <span
                                            class="text-gray-700"
                                            x-text="row[column.key]">
                                        </span>

                                    </template>

                                </td>

                            </template>

                        </tr>

                    </template>

                </tbody>


                {{-- ================================================= --}}
                {{-- TOTAL ROW --}}
                {{-- ================================================= --}}
                <tfoot
                    x-show="rows.length > 0"
                    class="bg-gray-50 border-t-2 border-gray-200">

                    <tr>

                        <template
                            x-for="(column, index) in columns"
                            :key="column.key">

                            <td
                                class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">

                                {{-- First column --}}
                                <template x-if="index === 0">
                                    <span>TOTAL</span>
                                </template>


                                {{-- Currency Total --}}
                                <template
                                    x-if="index !== 0 && column.type === 'currency'">

                                    <span
                                        x-text="
                                            formatCurrency(
                                                rows.reduce(
                                                    (total, row) =>
                                                        total + Number(row[column.key] ?? 0),
                                                    0
                                                )
                                            )
                                        ">
                                    </span>

                                </template>


                                {{-- Number Total --}}
                                <template
                                    x-if="index !== 0 && column.type === 'number'">

                                    <span
                                        x-text="
                                            formatNumber(
                                                rows.reduce(
                                                    (total, row) =>
                                                        total + Number(row[column.key] ?? 0),
                                                    0
                                                )
                                            )
                                        ">
                                    </span>

                                </template>

                            </td>

                        </template>

                    </tr>

                </tfoot>

            </table>

        </div>


        {{-- ========================================================= --}}
        {{-- FOOTER --}}
        {{-- ========================================================= --}}
        <div
            class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-2">

            <div class="text-xs text-gray-400">
                Generated via Faculty Association Claims Monitoring System
            </div>

            <div class="text-sm text-gray-500 italic">

                <span
                    x-text="
                        context.title === 'Collection Summary'
                            ? (summary?.period_label ?? 'All approved transfers')
                            : (rows.length + ' data record(s)')
                    ">
                </span>

            </div>

        </div>

    </div>
</template>
    
