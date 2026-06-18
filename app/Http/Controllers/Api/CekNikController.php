<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\Request;

class CekNikController extends Controller
{
    public function cek($nik)
    {
        $warga = Warga::where('nik', $nik)->first();

        if (!$warga) {
            return response()->json([
                'success' => false,
                'message' => 'NIK anda belum terdaftar silahkan datang ke pstore lenteng agung untuk pendaftaran NIK',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'NIK anda sudah terdaftar, silahkan download dan save BARCODE ini pengambilan paket berbagi ketika dibutuhkan',
            'data' => $warga
        ], 200);
    }
}
