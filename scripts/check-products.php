<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "Product IDs:\n";
Product::select('id', 'name', 'created_at')->limit(5)->get()->each(function($p) {
    echo $p->id . ' - ' . $p->name . ' - ' . $p->created_at . PHP_EOL;
});
