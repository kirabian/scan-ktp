<?php

namespace App\Livewire;

use App\Models\Warga;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ScanKtp extends Component
{
    use WithFileUploads;

    // OCR-extracted fields (editable by petugas)
    public string $nik = '';
    public string $nama = '';
    public string $tempat_lahir = '';
    public string $tanggal_lahir = '';
    public string $jenis_kelamin = '';
    public string $alamat = '';
    public string $rt = '';
    public string $rw = '';
    public string $kelurahan_desa = '';
    public string $kecamatan = '';

    // File upload
    public $foto_ktp;

    // UI state
    public string $message = '';
    public string $messageType = ''; // success, error, warning
    public string $wargaNama = '';
    public string $waktuAmbilInfo = '';
    public bool $isProcessing = false;

    /**
     * Validate and process the KTP verification.
     */
    public function verifikasiDanKonfirmasi(): void
    {
        $this->validate([
            'nik' => 'required|digits:16',
            'foto_ktp' => 'required|image|max:30720', // max 30MB from camera
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'foto_ktp.required' => 'Foto KTP wajib diambil.',
            'foto_ktp.image' => 'File harus berupa gambar.',
            'foto_ktp.max' => 'Ukuran foto maksimal 30MB.',
        ]);

        $this->isProcessing = true;
        $this->message = '';
        $this->messageType = '';
        $this->wargaNama = '';
        $this->waktuAmbilInfo = '';

        // 1. Search warga by NIK (indexed query, O(1))
        $warga = Warga::where('nik', $this->nik)->first();

        // 2. NIK not found
        if (!$warga) {
            $this->message = 'Warga dengan NIK ini Tidak Terdaftar sebagai Penerima Sembako!';
            $this->messageType = 'error';
            $this->isProcessing = false;
            return;
        }

        // 3. Already collected
        if ($warga->status_ambil) {
            $this->message = '⚠️ PERINGATAN: Warga ini sudah mengambil sembako pada ' . $warga->waktu_ambil->format('d/m/Y H:i') . '!';
            $this->messageType = 'warning';
            $this->wargaNama = $warga->nama;
            $this->waktuAmbilInfo = $warga->waktu_ambil->format('d/m/Y H:i');
            $this->isProcessing = false;
            return;
        }

        // 4. Process: Compress image & save
        $compressedImage = $this->compressKtpImage($this->foto_ktp->getRealPath());
        $filename = 'ktp_' . $this->nik . '_' . now()->format('Ymd_His') . '.jpg';

        // Save compressed image to secure storage
        Storage::disk('local')->put('secure_ktp/' . $filename, $compressedImage);

        // 5. Update warga record
        $warga->update([
            'status_ambil' => true,
            'waktu_ambil' => now(),
            'foto_ktp_path' => $filename,
            'petugas_id' => Auth::id(),
        ]);

        $this->message = '✅ BERHASIL: Sembako dapat diserahkan kepada ' . $warga->nama;
        $this->messageType = 'success';
        $this->wargaNama = $warga->nama;
        $this->isProcessing = false;

        // Reset form for next scan
        $this->resetExcept(['message', 'messageType', 'wargaNama']);
    }

    /**
     * Compress KTP image using GD Library to under 300KB.
     */
    private function compressKtpImage(string $sourcePath): string
    {
        $imageInfo = getimagesize($sourcePath);
        $mimeType = $imageInfo['mime'] ?? 'image/jpeg';

        // Create GD image from source
        $sourceImage = match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => imagecreatefromjpeg($sourcePath),
        };

        if (!$sourceImage) {
            return file_get_contents($sourcePath);
        }

        // Get original dimensions
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Resize if larger than 1200px on longest side (for KTP, this is plenty)
        $maxDimension = 1200;
        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth > $origHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int) ($origHeight * ($maxDimension / $origWidth));
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) ($origWidth * ($maxDimension / $origHeight));
            }

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($sourceImage);
            $sourceImage = $resized;
        }

        // Output as JPEG with progressive quality reduction to hit <300KB
        $quality = 85;
        do {
            ob_start();
            imagejpeg($sourceImage, null, $quality);
            $imageData = ob_get_clean();
            $quality -= 10;
        } while (strlen($imageData) > 307200 && $quality > 20); // 300KB = 307200 bytes

        imagedestroy($sourceImage);

        return $imageData;
    }

    /**
     * Reset the scan form for a new scan.
     */
    public function resetScan(): void
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.scan-ktp')
            ->layout('layouts.app')
            ->title('Scan KTP - Pembagian Sembako');
    }
}
