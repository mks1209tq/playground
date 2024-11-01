<x-app-layout>
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Preview Emirates ID Data</h2>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold mb-2">Extracted MRZ</h3>
                <p class="font-mono text-sm break-all bg-gray-100 p-2 rounded">{{ $mrz }}</p>
            </div>

            <div class="mb-4">
                <h3 class="text-lg font-semibold mb-2">Parsed Data</h3>
                <div class="grid grid-cols-2 gap-4">
                    @foreach($parsedData as $key => $value)
                        <div>
                            <label class="font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                            <p class="text-gray-900">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('emirates-id.parse') }}" method="POST" class="mt-6">
                @csrf
                <input type="hidden" name="mrz" value="{{ $mrz }}">
                
                <div class="flex justify-end gap-4">
                    <a href="{{ route('emirates-id.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Save Emirates ID
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>