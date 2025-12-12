<?php

require_once 'vendor/autoload.php';

use App\Services\PenjualanService;

// Test untuk fungsi calculatePembulatan yang sudah diperbaiki
$penjualanService = new PenjualanService(new App\Services\StokService());

echo "=== TEST PEMBULATAN SETELAH PERBAIKAN ===\n\n";

// Test kasus 1: 9999 → 10000 (kasus yang bermasalah)
$subtotal1 = 9999;
$pembulatan1 = $penjualanService->calculatePembulatan($subtotal1);
$grandTotal1 = $subtotal1 + $pembulatan1;
echo "Test Case 1 (kasus bermasalah):\n";
echo "  Subtotal: {$subtotal1}\n";
echo "  Pembulatan: {$pembulatan1}\n";
echo "  Grand Total: {$grandTotal1}\n";
echo "  Expected: 10000\n";
echo "  Status: " . ($grandTotal1 == 10000 ? "✅ PASS" : "❌ FAIL") . "\n\n";

// Test berbagai skenario pembulatan
$testCases = [
    // remainder = 0, no rounding
    1000 => "No rounding needed",
    2000 => "No rounding needed",
    5000 => "No rounding needed",
    
    // remainder 1-499 → bulat ke 500
    1001 => "Should round to 1500",
    1499 => "Should round to 1500", 
    2501 => "Should round to 3000",
    3499 => "Should round to 3500",
    
    // remainder >=500 → bulat ke 1000
    1500 => "Should round to 2000",
    1999 => "Should round to 2000",
    2500 => "Should round to 3000", 
    2999 => "Should round to 3000",
    
    // edge cases
    999 => "Should round to 1000",
    500 => "Should round to 1000",
    499 => "Should round to 500",
    1 => "Should round to 500",
];

echo "=== TEST SKENARIO PEMBULATAN ===\n\n";

foreach ($testCases as $subtotal => $description) {
    $pembulatan = $penjualanService->calculatePembulatan($subtotal);
    $grandTotal = $subtotal + $pembulatan;
    
    // Calculate expected values
    $remainder = $subtotal % 1000;
    if ($remainder == 0) {
        $expectedGrandTotal = $subtotal;
        $expectedPembulatan = 0;
    } elseif ($remainder >= 1 && $remainder <= 499) {
        $expectedGrandTotal = $subtotal + (500 - $remainder);
        $expectedPembulatan = 500 - $remainder;
    } else {
        $expectedGrandTotal = $subtotal + (1000 - $remainder);
        $expectedPembulatan = 1000 - $remainder;
    }
    
    $status = ($grandTotal == $expectedGrandTotal && $pembulatan == $expectedPembulatan) ? "✅" : "❌";
    
    echo "Test: {$subtotal} - {$description}\n";
    echo "  Subtotal: {$subtotal}\n";
    echo "  Remainder: {$remainder}\n";
    echo "  Pembulatan: {$pembulatan} (expected: {$expectedPembulatan})\n";
    echo "  Grand Total: {$grandTotal} (expected: {$expectedGrandTotal})\n";
    echo "  Status: {$status}\n\n";
}

echo "=== SELESAI TEST PEMBULATAN ===\n";
?>
