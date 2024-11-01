<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            <div>
                
            </div>



                <div class="p-6 text-gray-900 dark:text-gray-100 mt-6 py-6">
                    <a href="/emirates-id/extract-mrz">Extract MRZ</a>
                </div>
                <div class="p-6 text-gray-900 dark:text-gray-100 mt-6 py-6">
                    <a href="/emirates-id/parse">Parse</a>
                </div>
                <div class="p-6 text-gray-900 dark:text-gray-100 mt-6 py-6">
                    <a href="/scan-id">Scan ID</a>
                </div>
                <div class="p-6 text-gray-900 dark:text-gray-100 mt-6 py-6">
                    <form action="{{ route('scan-id') }}" method="POST">
                        @csrf
                        <textarea name="mrz" rows="4" cols="50"></textarea>
                        <button type="submit">Scan ID</button>
                    </form>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">MRZ Scanner</h1>
        <form action="{{ route('scan-mrz') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="pdf" class="block text-sm font-medium text-gray-700 mb-2">Upload MRZ PDF</label>
                <input type="file" id="pdf" name="pdf" accept="application/pdf" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Scan MRZ
                </button>
            </div>
        </form>
    </div>
            </div>
        </div>
    </div>
</x-app-layout>
