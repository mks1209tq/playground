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
            // ... existing code ...

            <div class="p-6 text-gray-900 dark:text-gray-100 mt-6 py-6">
    <h3>Additional PHP and Imagick Diagnostics</h3>
    <?php
    echo "PHP_SHLIB_SUFFIX: " . PHP_SHLIB_SUFFIX . "<br>";
    echo "PHP_ZTS: " . (defined('PHP_ZTS') && PHP_ZTS ? 'Enabled' : 'Disabled') . "<br>";
    echo "PHP_DEBUG: " . (defined('PHP_DEBUG') && PHP_DEBUG ? 'Enabled' : 'Disabled') . "<br>";
    echo "PHP_EXTENSION_DIR: " . PHP_EXTENSION_DIR . "<br>";
    
    $imagickDll = ini_get('extension_dir') . DIRECTORY_SEPARATOR . 'php_imagick.dll';
    echo "Imagick DLL exists: " . (file_exists($imagickDll) ? 'Yes' : 'No') . "<br>";
    if (file_exists($imagickDll)) {
        echo "Imagick DLL size: " . filesize($imagickDll) . " bytes<br>";
        echo "Imagick DLL last modified: " . date("Y-m-d H:i:s", filemtime($imagickDll)) . "<br>";
    }
    
    echo "Loaded extensions (with versions):<br>";
    foreach (get_loaded_extensions() as $ext) {
        echo $ext . ": " . phpversion($ext) . "<br>";
    }
    
    echo "Environment variables:<br>";
    print_r($_ENV);
    ?>
</div>


// ... existing code ...


                
                
            </div>
        </div>
    </div>
</x-app-layout>
