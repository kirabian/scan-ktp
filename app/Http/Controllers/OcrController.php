<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrController extends Controller
{
    public function processKtp(Request $request)
    {
        $request->validate([
            'foto_ktp' => 'required|image|max:30720', // max 30MB
        ]);

        $file = $request->file('foto_ktp');
        $mimeType = $file->getMimeType();
        $tempPath = $file->getRealPath();

        $ocrEngineSelected = $request->input('ocr_engine', 'auto');

        $text = false;
        $rawLogResponse = 'N/A';
        $processedImagePath = $tempPath;

        try {
            $processedImagePath = $this->preprocessImage($tempPath, $mimeType);

            if ($ocrEngineSelected === 'paddleocr') {
                $text = $this->processWithPaddleOcr($processedImagePath);
            } elseif ($ocrEngineSelected === 'ocrspace') {
                $text = $this->processWithOcrSpace($processedImagePath, $rawLogResponse);
            } elseif ($ocrEngineSelected === 'gas') {
                $text = $this->processWithGas($processedImagePath, $mimeType, $rawLogResponse);
            } else { // auto
                Log::info("Auto Fallback: Mencoba OCR.space terlebih dahulu...");
                $text = $this->processWithOcrSpace($processedImagePath, $rawLogResponse);

                if ($text === false || empty(trim($text))) {
                    Log::warning("Auto Fallback: OCR.space gagal/limit. Beralih ke Local PaddleOCR...");
                    $text = $this->processWithPaddleOcr($processedImagePath);
                }
            }

            if ($text === false || empty(trim($text))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses data menggunakan engine selected. Silakan gunakan opsi mesin lainnya.',
                ]);
            }

            // Log raw text untuk kebutuhan debug lokal
            try {
                $logDir = storage_path('logs');
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
                file_put_contents($logDir . '/ocr_raw.log', "[" . date('Y-m-d H:i:s') . "] Raw OCR Text:\n" . $text . "\n--------------------\n", FILE_APPEND);
            } catch (\Exception $e) {
                // Ignore log errors
            }

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

            // =================================================================
            // STRATEGI PERBAIKAN EXTRACTION BERDASARKAN ENGINE
            // =================================================================
            // Menggunakan Pembacaan Struktur Baris untuk PaddleOCR (Sangat Akurat)
            $lines = array_map('trim', explode("\n", $text));
            $values = [];

            foreach ($lines as $line) {
                if (preg_match('/(Loading weights|it\/s|%\|)/i', $line) || empty(trim($line))) {
                    continue;
                }

                $clean = preg_replace('/^(NIK|N1K|NlK|N\|K|NK|N1k|M1K|IIK|HIK|MK|Nama|Tempat\/?T\s*g\s*l\s*Lahir|Tempat|T\s*g\s*l|Lahir|Jenis\s*Kelamin|Kelamin|Gol\.?\s*Darah|Gol[,\.]?\s*Daral|Gol|Darah|Alamat|RT[\/\-]RW|RT\s*RW|RT|RW|Kel\/Desa|Kelurahan|Desa|Kecamatan|Kec|Agama|Status\s*Perkawinan|Status|Perkawinan|Pekerjaan|Kewarganegaraan|Berlaku\s*Hingga|Berlaku|Hingga)[\s:\-\.]*/i', '', $line);
                $clean = trim($clean);
                $clean = preg_replace('/^[:\-\.\=]+\s*/', '', $clean);

                if (strlen($clean) > 1 && !preg_match('/^[=\-\_\.\:\/]+$/', $clean)) {
                    $values[] = $clean;
                }
            }

            $rtrwIndex = -1;
            $usedIndices = [];

            foreach ($values as $i => $val) {
                if (empty($extractedData['nik'])) {
                    $sanitized = preg_replace('/[^\d]/', '', $val);
                    if (preg_match('/\d{16}/', $sanitized, $mNik)) {
                        $extractedData['nik'] = $mNik[0];
                        $usedIndices[] = $i;
                    }
                }

                if (empty($extractedData['tempat_tgl_lahir']) && preg_match('/^([A-Za-z\s\.\-]+)[,\.]?\s*(\d{2})\s*[\-\.]\s*(\d{2})\s*[\-\.]\s*(\d{4})/i', $val, $mTtl)) {
                    $ttlString = trim($mTtl[1]);
                    $usedIndices[] = $i;

                    if (empty($extractedData['nama']) && $i > 0) {
                        $namaIndex = $i - 1;
                        if (preg_match('/(LAHIR|LAH|LHR|TGL|GL|TMP)/i', $values[$namaIndex]) && isset($values[$namaIndex - 1])) {
                            $namaIndex--;
                        }
                        if (!preg_match('/\b\d{16}\b/', str_replace(' ', '', $values[$namaIndex]))) {
                            $cleanNama = preg_replace('/^[\/\s:\-]+/', '', $values[$namaIndex]);
                            $extractedData['nama'] = strtoupper(trim($cleanNama));
                            $usedIndices[] = $namaIndex;
                        }
                    }
                    $extractedData['tempat_tgl_lahir'] = trim($ttlString) . ', ' . $mTtl[2] . '-' . $mTtl[3] . '-' . $mTtl[4];
                }

                if (empty($extractedData['jenis_kelamin']) && preg_match('/(LAKI|PEREMPUAN)/i', $val, $mJk)) {
                    $extractedData['jenis_kelamin'] = preg_match('/PEREMPUAN/i', $mJk[1]) ? 'PEREMPUAN' : 'LAKI-LAKI';
                    $usedIndices[] = $i;
                }

                $valRtRw = str_replace(['O', 'o', 'I', 'l', '|'], ['0', '0', '1', '1', '1'], $val);
                if (empty($extractedData['rt_rw_ktp']) && preg_match('/(?<!\d)(\d{3})\s*[\/\|\-\\\:\.]?\s*(\d{3})(?!\d)/', $valRtRw, $m)) {
                    $extractedData['rt_rw_ktp'] = sprintf("%03d/%03d", (int)$m[1], (int)$m[2]);
                    $rtrwIndex = $i;
                    $usedIndices[] = $i;
                }
            }

            if ($rtrwIndex !== -1) {
                $kelIdx = $rtrwIndex + 1;
                $kecIdx = $rtrwIndex + 2;
                if (isset($values[$kelIdx]) && !in_array($kelIdx, $usedIndices)) {
                    $extractedData['kel_desa_ktp'] = strtoupper($values[$kelIdx]);
                }
                if (isset($values[$kecIdx]) && !in_array($kecIdx, $usedIndices)) {
                    $extractedData['kecamatan_ktp'] = strtoupper($values[$kecIdx]);
                }
                if ($rtrwIndex > 0 && isset($values[$rtrwIndex - 1])) {
                    $extractedData['alamat_ktp'] = strtoupper($values[$rtrwIndex - 1]);
                }
            }

            // Fallback NIK Global dari teks bersih jika pencarian baris meleset
            if (empty($extractedData['nik'])) {
                $cleanTextForNik = preg_replace('/.*(Loading weights|it\/s).*/i', '', $text);
                $sanitizedText = preg_replace('/[^\d]/', '', $cleanTextForNik);
                if (preg_match('/\d{16}/', $sanitizedText, $m)) {
                    $extractedData['nik'] = $m[0];
                }
            }

            // Koreksi typo OCR standard wilayah Jabodetabek
            $koreksiOcr = ['KAUDERES' => 'KALIDERES', 'SEMANAM' => 'SEMANAN', 'JAKATRA' => 'JAKARTA'];
            foreach ($koreksiOcr as $salah => $benar) {
                if (!empty($extractedData['nama'])) $extractedData['nama'] = str_replace($salah, $benar, $extractedData['nama']);
                if (!empty($extractedData['kel_desa_ktp'])) $extractedData['kel_desa_ktp'] = str_replace($salah, $benar, $extractedData['kel_desa_ktp']);
                if (!empty($extractedData['kecamatan_ktp'])) $extractedData['kecamatan_ktp'] = str_replace($salah, $benar, $extractedData['kecamatan_ktp']);
            }

            return response()->json(array_merge(['success' => true, 'raw_ocr_text' => $text], $extractedData));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memproses OCR: ' . $e->getMessage()], 500);
        } finally {
            if (isset($processedImagePath) && file_exists($processedImagePath) && $processedImagePath !== $tempPath) {
                @unlink($processedImagePath);
            }
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
        // Optimasi kecepatan: Turunkan maksimal lebar ke 800px agar memori & CPU tidak jebol di VPS
        if ($width > 800) {
            $scale = 800 / $width;
            $newWidth = 800;
            $newHeight = (int) ($height * $scale);
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        $processedPath = storage_path('app/ocr_prepared_' . uniqid() . '.jpg');
        imagejpeg($image, $processedPath, 90);
        imagedestroy($image);
        return $processedPath;
    }


    private function processWithOcrSpace($imagePath, &$rawLogResponse)
    {
        try {
            $apiKey = env('OCR_SPACE_API_KEY', 'helloworld');
            $response = Http::attach('file', file_get_contents($imagePath), 'ktp.jpg')->post('https://api.ocr.space/parse/image', [
                'apikey' => $apiKey,
                'language' => 'eng',
                'scale' => 'true',
                'OCREngine' => '2',
            ]);

            $rawLogResponse = $response->body();

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['error']) || (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing'] == true)) {
                    return false;
                }
                if (isset($result['ParsedResults'][0]['ParsedText'])) {
                    return $result['ParsedResults'][0]['ParsedText'];
                }
            }
        } catch (\Exception $e) {
            Log::error("OCR.space Error: " . $e->getMessage());
        }
        return false;
    }

    private function processWithGas($imagePath, $mimeType, &$rawLogResponse)
    {
        try {
            $gasUrl = env('GAS_OCR_URL');
            if (!$gasUrl) {
                Log::error("GAS_OCR_URL is not configured.");
                return false;
            }
            $base64Image = base64_encode(file_get_contents($imagePath));

            $response = Http::post($gasUrl, [
                'image' => $base64Image,
                'mimeType' => $mimeType,
            ]);

            $rawLogResponse = $response->body();

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['text'])) {
                    return $result['text'];
                }
            }
        } catch (\Exception $e) {
            Log::error("GAS OCR Error: " . $e->getMessage());
        }
        return false;
    }

    private function isPaddleOcrAvailable()
    {
        return true;
    }

    private function processWithPaddleOcr($imagePath)
    {
        $socketPath = '/tmp/paddleocr_daemon.sock';

        // Fallback ke exec() kalau daemon tidak jalan
        if (!file_exists($socketPath)) {
            Log::warning("PaddleOCR daemon tidak aktif, fallback ke exec()");
            return $this->processWithPaddleOcrExec($imagePath);
        }

        try {
            $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
            if (!$socket) throw new \Exception("Gagal buat socket");

            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 15, 'usec' => 0]);
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5,  'usec' => 0]);

            if (!socket_connect($socket, $socketPath)) {
                throw new \Exception("Gagal connect ke daemon");
            }

            socket_write($socket, $imagePath . "\n");

            $response = '';
            socket_set_nonblock($socket);
            $start = time();
            while (time() - $start < 60) {
                $chunk = @socket_read($socket, 8192);
                if ($chunk === false) {
                    usleep(100000);
                    continue;
                }
                if ($chunk === '') break;
                $response .= $chunk;
                if (str_ends_with(trim($response), '}')) break;
            }
            socket_set_block($socket);
            socket_close($socket);

            $data = json_decode(trim($response), true);

            if (isset($data['error'])) {
                throw new \Exception($data['error']);
            }

            return $data['text'] ?? false;
        } catch (\Exception $e) {
            Log::error("PaddleOCR Daemon Error: " . $e->getMessage());
            return false;
        }
    }

    // Method lama sebagai fallback
    private function processWithPaddleOcrExec($imagePath)
    {
        try {
            $pythonBinary = env('PYTHON_BINARY_PATH', 'python');
            $scriptPath = base_path('app/Services/paddle_ocr.py');
            $storagePath = storage_path('app');
            $command = 'HOME="' . $storagePath . '" FLAGS_use_onednn=0 FLAGS_use_mkldnn=0 "' . $pythonBinary . '" "' . $scriptPath . '" "' . $imagePath . '" 2>&1';

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            $resultText = implode("\n", $output);
            if ($returnVar !== 0 || str_starts_with($resultText, 'ERROR:')) {
                throw new \Exception($resultText);
            }
            return $resultText;
        } catch (\Exception $e) {
            Log::error("PaddleOCR Exec Error: " . $e->getMessage());
            return false;
        }
    }
}
