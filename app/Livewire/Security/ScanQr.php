<?php

namespace App\Livewire\Security;

use Livewire\Component;
use App\Models\Warga;
use App\Models\HistoriSedekah;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\ImageService;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ScanQr extends Component
{
    public $nik;
    public $manualNik = '';
    public $warga = null;
    public $errorMessage = '';
    public $warningMessage = '';
    public $statusPengambilan = null;
    public $showDoubleWarning = false;
    public $fotoWajahDarurat = null;
    public $showConfirmation = false;
    public $kategoriUsia = '';

    // Event properties
    public $activeEvents = [];
    public $selectedEventId = null;
    public $showEventSelector = false;
    public $currentEvent = null;

    protected $listeners = ['qrScanned'];

    public function mount()
    {
        $this->loadActiveEvents();
    }

    public function loadActiveEvents()
    {
        $this->activeEvents = Event::currentlyActive()->get()->toArray();

        if (count($this->activeEvents) === 1) {
            $this->selectedEventId = $this->activeEvents[0]['id'];
            $this->currentEvent = $this->activeEvents[0];
            $this->showEventSelector = false;
        } elseif (count($this->activeEvents) > 1) {
            $this->showEventSelector = true;
            $this->selectedEventId = null;
            $this->currentEvent = null;
        } else {
            $this->showEventSelector = false;
            $this->selectedEventId = null;
            $this->currentEvent = null;
        }
    }

    public function selectEvent($eventId)
    {
        $this->selectedEventId = $eventId;
        $this->currentEvent = Event::find($eventId)?->toArray();
        $this->showEventSelector = false;
    }

    public function qrScanned($nik)
    {
        $this->nik = str_replace(' ', '', trim($nik));
        $this->errorMessage = '';
        $this->warningMessage = '';
        $this->statusPengambilan = null;
        $this->showDoubleWarning = false;
        $this->fotoWajahDarurat = null;
        $this->showConfirmation = false;
        $this->kategoriUsia = '';

        $this->warga = Warga::where('nik', $this->nik)->first();

        if (!$this->warga) {
            $this->errorMessage = 'Data warga tidak ditemukan! (NIK Terdeteksi: ' . $this->nik . ').';
            $this->manualNik = $this->nik;
            return;
        }
        
        $umur = $this->warga->umur;
        if ($umur === '-' || !is_numeric($umur)) {
            $this->kategoriUsia = 'Tidak Diketahui';
        } else {
            if ($umur <= 11) $this->kategoriUsia = 'Anak-anak';
            elseif ($umur <= 25) $this->kategoriUsia = 'Remaja (Usia Aman)';
            elseif ($umur <= 45) $this->kategoriUsia = 'Dewasa (Usia Aman)';
            else $this->kategoriUsia = 'Lansia (Usia Aman)';
        }

        $today = Carbon::today();
        
        // Cek histori berdasarkan event yang dipilih (jika ada)
        $historiQuery = HistoriSedekah::where('warga_id', $this->warga->id);
        if ($this->selectedEventId) {
            $historiQuery->where('event_id', $this->selectedEventId);
        }
        $historiTerakhir = $historiQuery->orderBy('waktu_ambil', 'desc')->first();

        if ($historiTerakhir) {
            $eventLabel = $historiTerakhir->event ? ' (Event: ' . $historiTerakhir->event->judul . ')' : '';
            $this->statusPengambilan = 'Terakhir dapat sedekah: ' . $historiTerakhir->waktu_ambil->translatedFormat('d M Y H:i') . $eventLabel;
            
            if ($historiTerakhir->waktu_ambil->isSameDay($today)) {
                $this->showDoubleWarning = true;
                $this->warningMessage = 'Sudah di scan hari ini jam: ' . $historiTerakhir->waktu_ambil->format('H:i') . $eventLabel . '. Yakin mau dilanjutkan lagi?';
            }
        } else {
            // Cek juga histori tanpa event atau event lain untuk info
            $historiGlobal = HistoriSedekah::where('warga_id', $this->warga->id)
                ->orderBy('waktu_ambil', 'desc')
                ->first();
            if ($historiGlobal) {
                $eventLabel = $historiGlobal->event ? ' (Event: ' . $historiGlobal->event->judul . ')' : '';
                $this->statusPengambilan = 'Terakhir dapat sedekah: ' . $historiGlobal->waktu_ambil->translatedFormat('d M Y H:i') . $eventLabel;
                
                if ($historiGlobal->waktu_ambil->isSameDay($today)) {
                    $this->showDoubleWarning = true;
                    $this->warningMessage = 'Warga ini sudah di scan hari ini jam: ' . $historiGlobal->waktu_ambil->format('H:i') . $eventLabel . '. Yakin mau dilanjutkan untuk event ini?';
                }
            }
        }
    }

    public function searchManual()
    {
        $this->validate([
            'manualNik' => 'required|size:16'
        ], [
            'manualNik.required' => 'Kolom NIK wajib diisi.',
            'manualNik.size' => 'NIK harus persis 16 digit angka.'
        ]);
        
        $this->qrScanned($this->manualNik);
        $this->manualNik = '';
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
        if (!$this->warga) return;

        HistoriSedekah::create([
            'event_id' => $this->selectedEventId,
            'warga_id' => $this->warga->id,
            'petugas_security_id' => Auth::id(),
            'waktu_ambil' => now(),
        ]);

        $eventLabel = $this->currentEvent ? ' untuk event: ' . $this->currentEvent['judul'] : '';
        session()->flash('success', 'Data pengambilan sedekah berhasil dicatat' . $eventLabel . '.');
        $this->resetScan();
    }

    public function catatPengambilanGanda(ImageService $imageService, $fotoDataUrl)
    {
        if (!$this->warga) return;

        $fotoPath = $imageService->compressAndSaveSecurely($fotoDataUrl, 'darurat');

        HistoriSedekah::create([
            'event_id' => $this->selectedEventId,
            'warga_id' => $this->warga->id,
            'petugas_security_id' => Auth::id(),
            'waktu_ambil' => now(),
            'foto_penerima_path' => $fotoPath,
        ]);

        $eventLabel = $this->currentEvent ? ' untuk event: ' . $this->currentEvent['judul'] : '';
        session()->flash('success', 'Pengambilan ganda (darurat) berhasil dicatat beserta foto bukti' . $eventLabel . '.');
        $this->resetScan();
    }

    public function render()
    {
        return view('livewire.security.scan-qr');
    }
}
