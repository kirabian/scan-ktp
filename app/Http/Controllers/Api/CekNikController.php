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

        $data = $warga->toArray();
        $data['encrypted_nik'] = \Illuminate\Support\Facades\Crypt::encryptString($warga->nik);

        return response()->json([
            'success' => true,
            'message' => 'NIK anda sudah terdaftar, silahkan download dan save BARCODE ini',
            'data' => $data
        ], 200);
    }

    public function trackDownload(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|size:16',
        ]);

        $warga = Warga::where('nik', $request->nik)->first();

        if ($warga) {
            $warga->increment('qr_download_count');
            $warga->last_qr_download_at = now();
            $warga->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'NIK tidak ditemukan'], 404);
    }
}
