    <div
        x-show="alert.show"
        x-transition
        class="fixed bottom-6 right-6 z-50 max-w-sm">
        <div
            class="rounded-xl shadow-lg border p-4"
            :class="{
                'bg-green-50 border-green-200' : alert.type=='success',
                'bg-red-50 border-red-200' : alert.type=='error',
                'bg-yellow-50 border-yellow-200' : alert.type=='warning',
                'bg-blue-50 border-blue-200' : alert.type=='info'
            }">
            <div class="flex items-start justify-between">
                <div>
                    <h4
                        class="font-semibold"
                        :class="{
                            'text-green-700' : alert.type=='success',
                            'text-red-700' : alert.type=='error',
                            'text-yellow-700' : alert.type=='warning',
                            'text-blue-700' : alert.type=='info'
                        }"
                        x-text="alert.title">
                    </h4>
                    <p
                        class="mt-1 text-sm text-gray-600"
                        x-text="alert.message">
                    </p>
                </div>
                <button
                    @click="alert.show=false"
                    class="ml-4 text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>
        </div>
    </div>
