<?php

// Test script to verify pembulatan calculation
echo "Testing pembulatan calculation:\n\n";

// Test cases
$testCases = [
    9999,
    10500,
    1000,
    1500,
    10000
];

foreach ($testCases as $subtotal) {
    $remainder = fmod($subtotal, 1000);
    $remainder = round($remainder);
    
    $pembulatan = 0;
    if ($remainder == 0) {
        $pembulatan = 0;
    } elseif ($remainder >= 1 && $remainder <= 699) {
        $pembulatan = 500 - $remainder;
    } else if ($remainder >= 700 && $remainder <= 999) {
        $pembulatan = 1000 - $remainder;
    }
    
    $grandTotal = $subtotal + $pembulatan;
    
    echo "Subtotal: {$subtotal}\n";
    echo "Remainder: {$remainder}\n";
    echo "Pembulatan: {$pembulatan}\n";
    echo "Grand Total: {$grandTotal}\n";
    echo "---\n";
}

echo "\nExpected for 9999: Subtotal: 9999, Pembulatan: 1, Grand Total: 10000\n";
