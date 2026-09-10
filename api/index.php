<?php

// Buat direktori temporary yang dibutuhkan Laravel
$directories = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // Set storage path ke /tmp agar writable di Vercel
    $app->useStoragePath('/tmp/storage');

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    $response->send();

    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    // Tangkap eror asli secara langsung sebelum memicu eror sekunder pada view
    http_response_code(500);
    header('Content-Type: text/plain');
    
    $prev = $e->getPrevious();
    echo "=== ORIGINAL ERROR ===\n";
    echo ($prev ? $prev->getMessage() : $e->getMessage()) . "\n\n";
    echo "=== TRACE ===\n";
    echo ($prev ? $prev->getTraceAsString() : $e->getTraceAsString());
    exit;
}