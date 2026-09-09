<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Repositories\StockReportRepository;
use App\Models\Product;

$product = Product::first();
$companyId = $product->company_id;

$repo = app(StockReportRepository::class);
$filters = ['per_page' => 10];

// Test page 1
echo "=== PAGE 1 ===\n";
$result = $repo->paginate($companyId, $filters);
echo "Count: " . $result->count() . "\n";
echo "Has more pages: " . ($result->hasMorePages() ? 'Yes' : 'No') . "\n";

if ($result->hasMorePages()) {
    $nextCursor = $result->nextCursor()->encode();
    echo "Next cursor: {$nextCursor}\n";
    
    // Test page 2 dengan cursor
    echo "\n=== PAGE 2 (with cursor) ===\n";
    $filters['cursor'] = $nextCursor;
    $result2 = $repo->paginate($companyId, $filters);
    echo "Count: " . $result2->count() . "\n";
    echo "Has more pages: " . ($result2->hasMorePages() ? 'Yes' : 'No') . "\n";
    
    if ($result2->hasMorePages()) {
        $nextCursor2 = $result2->nextCursor()->encode();
        echo "Next cursor 2: {$nextCursor2}\n";
        
        // Test page 3
        echo "\n=== PAGE 3 (with cursor) ===\n";
        $filters['cursor'] = $nextCursor2;
        $result3 = $repo->paginate($companyId, $filters);
        echo "Count: " . $result3->count() . "\n";
        echo "Has more pages: " . ($result3->hasMorePages() ? 'Yes' : 'No') . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total products in DB: " . Product::count() . "\n";
echo "Expected pages: " . ceil(Product::count() / 10) . "\n";
