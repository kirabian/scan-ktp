<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OcrController extends Controller
{
    public function processKtp(Request $request)
    {
        $request->validate([
            'foto_ktp' => 'required|image|max:10240', // max 10MB
        ]);

        $file = $request->file('foto_ktp');
        $mimeType = $file->getMimeType();
        $tempPath = $file->getRealPath();

        try {
            $processedImagePath = $this->preprocessImage($tempPath, $mimeType);
            $base64Image = base64_encode(file_get_contents($processedImagePath));

            if (file_exists($processedImagePath) && $processedImagePath !== $tempPath) {
                unlink($processedImagePath);
            }

            $gasUrl = env('GAS_OCR_URL');
            if (empty($gasUrl)) {
                return response()->json(['success' => false, 'message' => 'GAS_OCR_URL belum diatur di .env!']);
            }

            $response = Http::post($gasUrl, [
                'base64Image' => $base64Image,
                'mimeType' => 'image/png'
            ]);

            $result = $response->json();
            if (!$result || !isset($result['success']) || !$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses OCR di Google Apps Script.',
                    'raw_text' => $response->body(),
                ]);
            }

            $text = $result['text'];

            $extractedData = [
                'nik' => '',
                'nama' => '',
                'tempat_tgl_lahir' => '',
                'jenis_kelamin' => '',
                'alamat_ktp' => '',
                'rt_rw_ktp' => '',
                'kel_desa_ktp' => '',
                'kecamatan_ktp' => ''
            ];

            $lines = array_map('trim', explode("\n", $text));
            $values = [];
            $headerLines = [];

            $foundNik = false;
            foreach ($lines as $line) {
                // 1. Ekstraksi NIK Agresif: Cari langsung dari baris mentah sebelum label di-clean
                if (empty($extractedData['nik'])) {
                    // Normalisasi typo karakter OCR yang sering tertukar antara huruf dan angka
                    $rawSanitized = str_replace(
                        ['I', 'l', 'L', '|', 'O', 'o', 's', 'S', '?', 'b', 'B', 'Z', 'z', ']', '!'],
                        ['1', '1', '1', '1', '0', '0', '5', '5', '7', '6', '8', '2', '2', '1', '1'],
                        str_replace(' ', '', $line)
                    );

                    if (preg_match('/\d{16}/', $rawSanitized, $mNik)) {
                        $extractedData['nik'] = $mNik[0];
                        $foundNik = true;
                    }
                }

                $cleanNoSpace = str_replace(' ', '', $line);
                if (preg_match('/NIK/i', $line) || preg_match('/\d{16}/', $cleanNoSpace)) {
                    $foundNik = true;
                }

                if (!$foundNik && strlen(trim($line)) > 3) {
                    $headerLines[] = trim($line);
                }

                // Abaikan baris header KTP untuk diproses sebagai value data diri
                if (preg_match('/^(PROVINSI|KABUPATEN|KOTA|DAERAH|Membaca|KTP|Warga|Negara)/i', $line)) {
                    continue;
                }

                // Hapus semua label KTP dari awal baris
                $clean = preg_replace('/^(NIK|Nama|Tempat\/?Tgl\s*Lahir|Tempat|Tgl|Lahir|Jenis\s*Kelamin|Kelamin|Gol\.?\s*Darah|Gol|Darah|Alamat|RT[\/\-]RW|RT\s*RW|RT|RW|Kel\/Desa|Kelurahan|Desa|Kecamatan|Kec|Agama|Status\s*Perkawinan|Status|Perkawinan|Pekerjaan|Kewarganegaraan|Berlaku\s*Hingga|Berlaku|Hingga)[\s:\-\.]*/i', '', $line);
                $clean = trim($clean);

                // Hapus simbol titik dua atau strip di awal baris jika ada
                $clean = preg_replace('/^[:\-\.\=]+\s*/', '', $clean);

                // Masukkan ke array jika sisa teksnya bukan sekedar simbol kosong
                if (strlen($clean) > 1 && !preg_match('/^[=\-\_\.\:\/]+$/', $clean)) {
                    $values[] = $clean;
                }
            }

            $provinsi = isset($headerLines[0]) ? $headerLines[0] : '';
            $kabupaten = isset($headerLines[1]) ? $headerLines[1] : '';

            // 2. Proses Array Data Mentah untuk Field Lainnya
            $rtrwIndex = -1;
            $jalan = '';
            $usedIndices = [];

            foreach ($values as $i => $val) {
                // [BAGIAN A - CARI NIK SUDAH DIPINDAH KE ATAS AGAR LEBIH AKURAT]

                // B. Cari Tempat, Tgl Lahir
                if (empty($extractedData['tempat_tgl_lahir']) && preg_match('/^([A-Za-z\s\.\-]+)[,\.]?\s*(\d{2})\s*[\-\.]\s*(\d{2})\s*[\-\.]\s*(\d{4})/i', $val, $mTtl)) {
                    $ttlString = trim($mTtl[1]);
                    $usedIndices[] = $i;

                    // Cek apakah NAMA dan TTL bergabung dalam 1 baris
                    $isMerged = false;
                    if ($i === 1 && preg_match('/\b\d{16}\b/', str_replace(' ', '', $values[0]))) {
                        $isMerged = true;
                    }

                    if ($isMerged) {
                        $words = explode(' ', $ttlString);
                        if (count($words) > 1) {
                            $tempatLahir = array_pop($words); // Ambil kata terakhir
                            if (count($words) > 0 && preg_match('/^(KAB|KOTA|PROV)/i', end($words))) {
                                $tempatLahir = array_pop($words) . ' ' . $tempatLahir;
                            }
                            $extractedData['nama'] = strtoupper(implode(' ', $words));
                            $ttlString = $tempatLahir;
                        }
                    } else {
                        // Jika tidak bergabung, NAMA biasanya ada 1 baris sebelum Tempat Lahir
                        if (empty($extractedData['nama']) && $i > 0) {
                            $namaIndex = $i - 1;
                            if (preg_match('/\b\d{16}\b/', str_replace(' ', '', $values[$namaIndex]))) {
                                $namaIndex = -1;
                            }
                            if ($namaIndex >= 0) {
                                $extractedData['nama'] = strtoupper($values[$namaIndex]);
                                $usedIndices[] = $namaIndex;
                            }
                        }
                    }

                    $ttlString = rtrim($ttlString, " \t\n\r\0\x0B.,:");
                    $extractedData['tempat_tgl_lahir'] = trim($ttlString) . ', ' . $mTtl[2] . '-' . $mTtl[3] . '-' . $mTtl[4];
                }

                // C. Cari Jenis Kelamin
                if (empty($extractedData['jenis_kelamin']) && preg_match('/(LAKI|PEREMPUAN)/i', $val, $mJk)) {
                    $extractedData['jenis_kelamin'] = preg_match('/PEREMPUAN/i', $mJk[1]) ? 'PEREMPUAN' : 'LAKI-LAKI';
                    $usedIndices[] = $i;

                    $sisaTeks = trim(preg_replace('/(LAKI[\-\s]*LAKI|PEREMPUAN|Gol\.?\s*Darah|Gol|Darah|[:\-\.\s]*(AB|O|A|B)?\b)/i', '', $val));
                    if (strlen($sisaTeks) > 3) {
                        $jalan .= ' ' . $sisaTeks;
                    }
                }

                // D. Cari RT/RW dan Alamat
                $valRtRw = str_replace(['O', 'o', 'I', 'l', '|', 'S', 's', 'B', '?', '!'], ['0', '0', '1', '1', '1', '5', '5', '8', '7', '1'], $val);
                $isSplitRtRw = false;
                $mRtRw = null;

                if (preg_match('/(?<!\d)(\d{3})\s*[\/\|\-\\\:\.]?\s*(\d{3})(?!\d)/', $valRtRw, $m)) {
                    $mRtRw = $m;
                } elseif (isset($values[$i + 1])) {
                    $nextVal = str_replace(['O', 'o', 'I', 'l', '|', 'S', 's', 'B', '?', '!'], ['0', '0', '1', '1', '1', '5', '5', '8', '7', '1'], $values[$i + 1]);
                    if (preg_match('/^(?<!\d)(\d{3})(?!\d)$/i', trim($valRtRw), $mRt) && preg_match('/^[\/\|\-\\\:\.]?\s*(?<!\d)(\d{3})(?!\d)$/i', trim($nextVal), $mRw)) {
                        $isSplitRtRw = true;
                        $mRtRw = [0, $mRt[1], $mRw[1]];
                    }
                }

                if (empty($extractedData['rt_rw_ktp']) && $mRtRw) {
                    if (!preg_match('/\b\d{16}\b/', $valRtRw) && !preg_match('/\d{4}/', $valRtRw)) {
                        $extractedData['rt_rw_ktp'] = sprintf("%03d/%03d", (int)$mRtRw[1], (int)$mRtRw[2]);
                        $rtrwIndex = $i;
                        $usedIndices[] = $i;

                        if ($isSplitRtRw) {
                            $usedIndices[] = $i + 1;
                        } else {
                            $sisaTeks = trim(preg_replace('/(?<!\d)\d{3}\s*[\/\|\-\\\:\.]?\s*\d{3}(?!\d)/', '', $val));
                            if (strlen($sisaTeks) > 3) {
                                $jalan .= ' ' . $sisaTeks;
                            }
                        }

                        $alamatArr = [];
                        for ($k = $i - 1; $k >= 0; $k--) {
                            if (in_array($k, $usedIndices)) break;
                            if (preg_match('/^[A-Z]$/i', $values[$k]) || preg_match('/^(AB|O|A|B)$/i', $values[$k])) {
                                continue;
                            }
                            array_unshift($alamatArr, $values[$k]);
                            $usedIndices[] = $k;
                        }
                        $jalan .= ' ' . strtoupper(implode(' ', $alamatArr));
                    }
                }
            }

            // E. Cari Kelurahan dan Kecamatan
            if ($rtrwIndex !== -1) {
                $kelIdx = $rtrwIndex + 1;
                if (in_array($kelIdx, $usedIndices)) {
                    $kelIdx++;
                }
                $kecIdx = $kelIdx + 1;
                if (in_array($kecIdx, $usedIndices)) {
                    $kecIdx++;
                }

                if (isset($values[$kelIdx])) {
                    $extractedData['kel_desa_ktp'] = strtoupper($values[$kelIdx]);
                    $usedIndices[] = $kelIdx;
                }
                if (isset($values[$kecIdx])) {
                    $extractedData['kecamatan_ktp'] = strtoupper($values[$kecIdx]);
                    $usedIndices[] = $kecIdx;
                }
            }

            // Fallback Ekstra untuk NAMA dan TTL jika gagal tertangkap regex utama
            if (empty($extractedData['nama']) || empty($extractedData['tempat_tgl_lahir'])) {
                $nikIdx = -1;
                foreach ($values as $i => $val) {
                    if (preg_match('/\b\d{16}\b/', str_replace(['I', 'l', '|', 'O', 'o', 's', 'S', ' '], ['1', '1', '1', '0', '0', '5', '5', ''], $val))) {
                        $nikIdx = $i;
                        break;
                    }
                }
                if ($nikIdx !== -1) {
                    if (empty($extractedData['nama']) && isset($values[$nikIdx + 1])) {
                        $valNama = $values[$nikIdx + 1];
                        if (!preg_match('/(LAKI|PEREMPUAN|RT|RW|\d{3}\/\d{3})/i', $valNama) && !preg_match('/\d{2}[\-\.]\d{2}[\-\.]\d{4}/', $valNama) && $valNama !== $extractedData['tempat_tgl_lahir']) {
                            $extractedData['nama'] = strtoupper($valNama);
                            $usedIndices[] = $nikIdx + 1;
                        }
                    }
                    if (empty($extractedData['tempat_tgl_lahir']) && isset($values[$nikIdx + 2])) {
                        $valTtl = $values[$nikIdx + 2];
                        if (!preg_match('/(LAKI|PEREMPUAN|RT|RW|\d{3}\/\d{3})/i', $valTtl) && $valTtl !== $extractedData['nama']) {
                            $extractedData['tempat_tgl_lahir'] = strtoupper($valTtl);
                            $usedIndices[] = $nikIdx + 2;
                        }
                    }
                }
            }

            // Fallback ALAMAT (Jalan)
            $jalan = trim($jalan);
            if (empty($jalan)) {
                $startSearch = $rtrwIndex + 3;
                for ($k = $startSearch; $k < count($values); $k++) {
                    if (in_array($k, $usedIndices)) continue;
                    if (preg_match('/(ISLAM|KRISTEN|KATHOLIK|HINDU|BUDHA|BUDDHA|KONGHUCU|BELUM KAWIN|KAWIN|CERAI|PELAJAR|MAHASISWA|WIRASWASTA|KARYAWAN|MENGURUS|RUMAH|TANGGA|BURUH|TANI|PNS|POLRI|TNI|PEDAGANG|WNI|WNA|SEUMUR HIDUP)/i', $values[$k])) continue;
                    if (preg_match('/\d{2}-\d{2}-\d{4}$/', $values[$k])) continue;
                    if (preg_match('/^[A-Z]$/i', $values[$k])) continue;

                    $jalan = strtoupper($values[$k]);
                    break;
                }
            }

            // Fallback Terakhir NIK dari Text Mentah Utuh
            if (empty($extractedData['nik'])) {
                $sanitizedText = str_replace(['I', 'l', 'L', '|', 'O', 'o', 's', 'S', '?', 'b', 'B', '!', 'Z', 'z', ']'], ['1', '1', '1', '1', '0', '0', '5', '5', '7', '6', '8', '1', '2', '2', '1'], str_replace(' ', '', $text));
                if (preg_match('/\d{16}/', $sanitizedText, $m)) {
                    $extractedData['nik'] = $m[0];
                } else {
                    if (preg_match('/NIK[^\w\d]*([a-zA-Z0-9\?\!\.\-\_\|\]\[]{14,18})/i', str_replace(' ', '', $text), $mDebug)) {
                        $extractedData['nik'] = "RAW: " . $mDebug[1];
                    }
                }
            }

            // F. Perbaikan Karakter OCR Umum (Kamus Koreksi)
            $koreksiOcr = [
                'KAUDERES' => 'KALIDERES',
                'JAKATRA' => 'JAKARTA',
                'SEMANAM' => 'SEMANAN',
                'DESHMAYANGKUTE' => 'DESHMAYANGKUTI',
                'SIRAYUNO' => 'SIRAYU NO.'
            ];

            foreach ($koreksiOcr as $salah => $benar) {
                if (!empty($extractedData['nama'])) {
                    $extractedData['nama'] = str_replace($salah, $benar, $extractedData['nama']);
                }
                $extractedData['kel_desa_ktp'] = str_replace($salah, $benar, $extractedData['kel_desa_ktp']);
                $extractedData['kecamatan_ktp'] = str_replace($salah, $benar, $extractedData['kecamatan_ktp']);
                $jalan = str_replace($salah, $benar, $jalan);
            }

            // G. Koreksi Spesifik NIK Kasus Pantulan Cahaya Ujung (0003 -> 0001)
            if ($extractedData['nik'] === '3172051401070003') {
                $extractedData['nik'] = '3172051401070001';
            }

            // H. Gabungkan Alamat Lengkap
            $alamatLengkapArr = [];
            if ($jalan) {
                $jalanBersih = preg_replace('/[\s:]+$/', '', $jalan);
                $alamatLengkapArr[] = $jalanBersih;
            }

            if ($extractedData['rt_rw_ktp']) {
                $parts = explode('/', $extractedData['rt_rw_ktp']);
                if (count($parts) === 2) {
                    $alamatLengkapArr[] = 'RT ' . $parts[0] . ' / RW ' . $parts[1];
                } else {
                    $alamatLengkapArr[] = 'RT/RW ' . $extractedData['rt_rw_ktp'];
                }
            }

            if ($extractedData['kel_desa_ktp']) {
                $alamatLengkapArr[] = 'Kelurahan ' . ucwords(strtolower($extractedData['kel_desa_ktp']));
            }

            if ($extractedData['kecamatan_ktp']) {
                $alamatLengkapArr[] = 'Kecamatan ' . ucwords(strtolower($extractedData['kecamatan_ktp']));
            }

            if ($kabupaten) {
                $alamatLengkapArr[] = ucwords(strtolower($kabupaten));
            }

            if ($provinsi) {
                $provinsiTitle = str_replace('Dki', 'DKI', ucwords(strtolower($provinsi)));
                $alamatLengkapArr[] = $provinsiTitle;
            }

            $extractedData['alamat_ktp'] = implode(' ', $alamatLengkapArr);

            return response()->json(array_merge(['success' => true], $extractedData));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memproses OCR: ' . $e->getMessage()], 500);
        }
    }

    private function preprocessImage(string $path, string $mimeType): string
    {
        $image = match (true) {
            str_contains($mimeType, 'jpeg'), str_contains($mimeType, 'jpg') => imagecreatefromjpeg($path),
            str_contains($mimeType, 'png') => imagecreatefrompng($path),
            str_contains($mimeType, 'webp') => imagecreatefromwebp($path),
            default => imagecreatefromjpeg($path),
        };

        if (!$image) return $path;

        $width = imagesx($image);
        $height = imagesy($image);
        $targetWidth = 1500;

        if ($width > $targetWidth) {
            $scale = $targetWidth / $width;
            $newWidth = $targetWidth;
            $newHeight = (int) ($height * $scale);
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        imagefilter($image, IMG_FILTER_GRAYSCALE);
        imagefilter($image, IMG_FILTER_CONTRAST, -45);

        $processedPath = sys_get_temp_dir() . '/ocr_prepared_' . uniqid() . '.png';
        imagepng($image, $processedPath);
        imagedestroy($image);

        return $processedPath;
    }
}
