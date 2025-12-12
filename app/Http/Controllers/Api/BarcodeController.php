<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Services\PenjualanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BarcodeController extends Controller
{
    protected $penjualanService;

    public function __construct(PenjualanService $penjualanService)
    {
        $this->penjualanService = $penjualanService;
    }


    /**
     * Endpoint untuk mendapatkan info paket yang sedang applicable
     */
    public function getCurrentPaket(Request $request): JsonResponse
    {
        try {
            $cartDetails = $request->input('cart_details', []);
            
            $paketInfo = $this->penjualanService->getCurrentApplicablePaket($cartDetails);

            return response()->json([
                'success' => true,
                'data' => [
                    'paket_info' => $paketInfo,
                    'cart_items_count' => count($cartDetails)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
