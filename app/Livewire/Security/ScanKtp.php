<?php

namespace App\Livewire\Security;

use Livewire\Component;
use App\Models\Warga;
use App\Models\HistoriSedekah;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\ImageService;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ScanKtp extends Component
{
    public $nik;
    public $warga = null;
    public $errorMessage = '';
    public $warningMessage = '';
    public $statusPengambilan = null;
    public $showDoubleWarning = false;
    public $fotoWajahDarurat = null;
    public $showConfirmation = false;

    protected $listeners = ['nikScanned'];

    public function nikScanned($nik)
    {
        $this->nik = $nik;
        $this->errorMessage = '';
        $this->warningMessage = '';
        $this->statusPengambilan = null;
        $this->showDoubleWarning = false;
        $this->fotoWajahDarurat = null;
        $this->showConfirmation = false;

        $this->warga = Warga::where('nik', $this->nik)->first();

        if (!$this->warga) {
            $this->errorMessage = 'Warga Belum Terdaftar! Silahkan ke Petugas DATA untuk Registrasi.';
            return;
        }

        $today = Carbon::today();
        
        $historiTerakhir = HistoriSedekah::where('warga_id', $this->warga->id)
                                         ->orderBy('waktu_ambil', 'desc')
                                         ->first();

        if ($historiTerakhir) {
            $this->statusPengambilan = 'Terakhir dapat sedekah: ' . $historiTerakhir->waktu_ambil->translatedFormat('d M Y H:i');
            
            if ($historiTerakhir->waktu_ambil->isSameDay($today)) {
                $this->showDoubleWarning = true;
                $this->warningMessage = 'Sudah di scan hari ini jam: ' . $historiTerakhir->waktu_ambil->format('H:i') . '. Yakin mau dilanjutkan lagi?';
            }
        }
    }

    public function handleMasuk()
    {
        if ($this->showDoubleWarning) {
            $this->showConfirmation = true;
        } else {
            $this->catatPengambilanNormal();
        }
    }

    public function resetScan()
    {
        $this->reset(['nik', 'warga', 'errorMessage', 'warningMessage', 'statusPengambilan', 'showDoubleWarning', 'fotoWajahDarurat', 'showConfirmation']);
        $this->dispatch('resetCamera');
    }

    public function catatPengambilanNormal()
    {
        HistoriSedekah::create([
            'warga_id' => $this->warga->id,
            'petugas_security_id' => Auth::id(),
            'waktu_ambil' => now(),
        ]);

        session()->flash('success', 'Data pengambilan sedekah berhasil dicatat.');
        $this->resetScan();
    }

    public function catatPengambilanGanda(ImageService $imageService, $fotoDataUrl)
    {
        // $fotoDataUrl dikirim dari frontend (kamera darurat)
        $fotoPath = $imageService->compressAndSaveSecurely($fotoDataUrl, 'darurat');

        HistoriSedekah::create([
            'warga_id' => $this->warga->id,
            'petugas_security_id' => Auth::id(),
            'waktu_ambil' => now(),
            'foto_penerima_path' => $fotoPath,
        ]);

        session()->flash('success', 'Pengambilan ganda (darurat) berhasil dicatat beserta foto bukti.');
        $this->resetScan();
    }

    public function render()
    {
        return view('livewire.security.scan-ktp');
    }
}
