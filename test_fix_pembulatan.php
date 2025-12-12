<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app = $kernel->bootstrap();

use App\Services\PenjualanService;
use Illuminate\Support\Facades\Auth;

// Mock auth
Auth::shouldReceive('id')->andReturn(1);

echo "Testing fixed pembulatan logic:\n\n";

// Test case yang bermasalah sebelumnya
$testDetails = [
    [
        'barang_id' => 1,
        'satuan_id' => 1,
        'tipe_harga' => 'eceran',
        'qty' => 1,
        'harga_jual' => 9999 // Hasil akhir yang seharusnya 9999
    ]
];

$penjualanService = new PenjualanService(app(App\Services\StokService::class));

try {
    $result = $penjualanService->calculateSubtotalAndPembulatan($testDetails);
    
    echo "Test case: harga_jual = 9999\n";
    echo "Subtotal: {$result['subtotal']}\n";
    echo "Pembulatan: {$result['pembulatan']}\n";
    echo "Grand Total: {$result['grand_total']}\n";
    
    // Verify expected result
    $expectedSubtotal = 9999;
    $expectedPembulatan = 1; // 9999 % 1000 = 999, so 1000 - 999 = 1
    $expectedGrandTotal = 10000; // 9999 + 1
    
    if ($result['subtotal'] == $expectedSubtotal && 
        $result['pembulatan'] == $expectedPembulatan && 
        $result['grand_total'] == $expectedGrandTotal) {
        echo "✅ Test PASSED - Pembulatan sudah benar!\n";
    } else {
        echo "❌ Test FAILED\n";
        echo "Expected: Subtotal={$expectedSubtotal}, Pembulatan={$expectedPembulatan}, Grand Total={$expectedGrandTotal}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTesting another case:\n\n";

// Test case dengan nilai yang berbeda
$testDetails2 = [
    [
        'barang_id' => 1,
        'satuan_id' => 1,
        'tipe_harga' => 'eceran',
        'qty' => 1,
        'harga_jual' => 15500 // Should result in pembulatan = 500
    ]
];

try {
    $result2 = $penjualanService->calculateSubtotalAndPembulatan($testDetails2);
    
    echo "Test case: harga_jual = 15500\n";
    echo "Subtotal: {$result2['subtotal']}\n";
    echo "Pembulatan: {$result2['pembulatan']}\n";
    echo "Grand Total: {$result2['grand_total']}\n";
    
    // Verify expected result
    $expectedSubtotal2 = 15500;
    $expectedPembulatan2 = 500; // 15500 % 1000 = 500, so no pembulatan needed
    $expectedGrandTotal2 = 15500; // 15500 + 0
    
    if ($result2['subtotal'] == $expectedSubtotal2 && 
        $result2['pembulatan'] == $expectedPembulatan2 && 
        $result2['grand_total'] == $expectedGrandTotal2) {
        echo "✅ Test PASSED - Pembulatan sudah benar!\n";
    } else {
        echo "❌ Test FAILED\n";
        echo "Expected: Subtotal={$expectedSubtotal2}, Pembulatan={$expectedPembulatan2}, Grand Total={$expectedGrandTotal2}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
