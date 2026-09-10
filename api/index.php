<?php

use Illuminate\Http\Request;

// Vercel's filesystem is ephemeral and must not be used for request state.
$serverlessDefaults = [
    'APP_DEBUG' => 'false',
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'DEBUGBAR_ENABLED' => 'false',
    'DEBUGBAR_STORAGE_ENABLED' => 'false',
    'DEBUGBAR_INJECT' => 'false',
];
$isVercel = getenv('VERCEL') === '1' || getenv('NOW_REGION') !== false;

foreach ($serverlessDefaults as $key => $value) {
    $currentValue = getenv($key);
    $mustReplaceFilesystemState = $isVercel
        && in_array($key, ['SESSION_DRIVER', 'CACHE_STORE'], true)
        && $currentValue === 'file';

    if ($currentValue === false || $mustReplaceFilesystemState) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// Buat direktori temporary yang dibutuhkan
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

// Hapus cache bootstrap jika terbawa dari lokal
@unlink(__DIR__ . '/../bootstrap/cache/config.php');
@unlink(__DIR__ . '/../bootstrap/cache/routes.php');
@unlink(__DIR__ . '/../bootstrap/cache/packages.php');
@unlink(__DIR__ . '/../bootstrap/cache/services.php');

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Pindahkan storage & bootstrap cache ke /tmp
    $app->useStoragePath('/tmp/storage');
    $app->useBootstrapPath('/tmp/bootstrap');

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Request::capture()
    );

    $response->send();

    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "=== ROOT CAUSE EXCEPTION ===\n";
    echo $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "=== TRACE ===\n";
    echo $e->getTraceAsString();
    exit;
}