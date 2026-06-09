<?php

namespace App\Livewire\Admin;

use App\Models\Warga;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]
class WargaList extends Component
{
    use WithPagination;

    public $search = '';
    
    public $selectedWarga = null;
    public $isModalOpen = false;

    public function render()
    {
        $wargas = Warga::where('nik', 'like', '%' . $this->search . '%')
            ->orWhere('nama', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.warga-list', [
            'wargas' => $wargas,
        ]);
    }

    public function viewDetails($id)
    {
        $this->selectedWarga = Warga::findOrFail($id);
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->selectedWarga = null;
    }

    public function deleteWarga($id)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $warga = Warga::findOrFail($id);

        // Hapus foto jika ada
        if ($warga->foto_ktp_path) {
            Storage::disk('local')->delete($warga->foto_ktp_path);
        }
        if ($warga->foto_wajah_path) {
            Storage::disk('local')->delete($warga->foto_wajah_path);
        }

        // Hapus histori sedekah terkait jika ada? Atau biarkan foreign key cascade yg urus (kalau ada)
        // Jika tidak ada cascade, kita hapus manual supaya tidak error.
        $warga->historiSedekahs()->delete(); // asumsikan relasi ada

        $warga->delete();

        session()->flash('success', 'Data Warga beserta fotonya berhasil dihapus.');
    }
}
