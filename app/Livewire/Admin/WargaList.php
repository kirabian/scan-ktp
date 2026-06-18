<?php

namespace App\Livewire\Admin;

use App\Models\Warga;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class WargaList extends Component
{
    use WithPagination;

    public $search = '';
    
    #[Url]
    public $filter_gender = '';
    #[Url]
    public $filter_age = '';
    #[Url]
    public $filter_district = '';
    #[Url]
    public $filter_village = '';
    
    public $selectedWarga = null;
    public $isModalOpen = false;

    // Edit states
    public $isEditModalOpen = false;
    public $editId;
    public $editNik;
    public $editNama;
    public $editTempatTglLahir;
    public $editJenisKelamin;
    public $editAlamatKtp;
    public $editRtRwKtp;
    public $editKelDesaKtp;
    public $editKecamatanKtp;
    public $editNoWaHp;
    public $editPekerjaan;

    public function render()
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('wargas', 'created_by_user_id')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        }

        $query = Warga::with('createdBy');

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nik', 'like', $searchTerm)
                  ->orWhere('nama', 'like', $searchTerm);
            });
        }

        if (!empty($this->filter_gender)) {
            $query->where('jenis_kelamin', $this->filter_gender);
        }

        if (!empty($this->filter_district)) {
            $query->where('kecamatan_ktp', $this->filter_district);
        }

        if (!empty($this->filter_village)) {
            $query->where('kel_desa_ktp', $this->filter_village);
        }

        // Apply age filter in PHP after fetching if $filter_age is set
        if (!empty($this->filter_age)) {
            // Since age is computed, we need to filter after fetching
            // A more efficient way is to compute the year of birth from the age and use SQL, but 
            // `tempat_tgl_lahir` is a free text string, making SQL extraction hard.
            // So we'll fetch them, filter, then paginate manually, OR we can fetch all and use collection pagination.
            // To keep simple pagination, we will fetch IDs of wargas that match the age, then do a whereIn.
            // But if dataset is huge, this is slow. For now, we'll do the whereIn approach.
            $allMatchingAgeIds = [];
            foreach (Warga::select('id', 'tempat_tgl_lahir')->get() as $w) {
                if ((string)$w->umur === (string)$this->filter_age) {
                    $allMatchingAgeIds[] = $w->id;
                }
            }
            // Providing all 4 arguments to whereIn to satisfy IDE static analysis
            $query->whereIn('id', $allMatchingAgeIds, 'and', false);
        }

        $wargas = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.warga-list', [
            'wargas' => $wargas,
        ]);
    }

    public function viewDetails($id)
    {
        $this->selectedWarga = Warga::with('createdBy')->findOrFail($id);
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->selectedWarga = null;
    }

    public function editWarga($id)
    {
        if (!in_array(Auth::user()?->role, ['admin', 'data'])) {
            abort(403, 'Unauthorized access');
        }

        $warga = Warga::findOrFail($id);
        $this->editId = $warga->id;
        $this->editNik = $warga->nik;
        $this->editNama = $warga->nama;
        $this->editTempatTglLahir = $warga->tempat_tgl_lahir;
        $this->editJenisKelamin = $warga->jenis_kelamin;
        $this->editAlamatKtp = $warga->alamat_ktp;
        $this->editRtRwKtp = $warga->rt_rw_ktp;
        $this->editKelDesaKtp = $warga->kel_desa_ktp;
        $this->editKecamatanKtp = $warga->kecamatan_ktp;
        $this->editNoWaHp = $warga->no_wa_hp;
        $this->editPekerjaan = $warga->pekerjaan;
        
        $this->isEditModalOpen = true;
    }

    public function closeEditModal()
    {
        $this->isEditModalOpen = false;
        $this->reset(['editId', 'editNik', 'editNama', 'editTempatTglLahir', 'editJenisKelamin', 'editAlamatKtp', 'editRtRwKtp', 'editKelDesaKtp', 'editKecamatanKtp', 'editNoWaHp', 'editPekerjaan']);
    }

    public function updateWarga()
    {
        if (!in_array(Auth::user()?->role, ['admin', 'data'])) {
            abort(403, 'Unauthorized access');
        }

        $this->validate([
            'editNik' => 'required|string|size:16|unique:wargas,nik,' . $this->editId,
            'editNama' => 'required|string|max:255',
            'editAlamatKtp' => 'required|string',
            'editNoWaHp' => 'required|string',
            'editPekerjaan' => 'required|string',
        ], [
            'editNik.required' => 'NIK wajib diisi.',
            'editNik.size' => 'NIK harus persis 16 digit.',
            'editNik.unique' => 'NIK ini sudah terdaftar.',
            'editNama.required' => 'Nama wajib diisi.',
            'editAlamatKtp.required' => 'Alamat wajib diisi.',
            'editNoWaHp.required' => 'No HP wajib diisi.',
            'editPekerjaan.required' => 'Pekerjaan wajib diisi.',
        ]);

        $warga = Warga::findOrFail($this->editId);
        $warga->update([
            'nik' => $this->editNik,
            'nama' => $this->editNama,
            'tempat_tgl_lahir' => $this->editTempatTglLahir,
            'jenis_kelamin' => $this->editJenisKelamin,
            'alamat_ktp' => $this->editAlamatKtp,
            'rt_rw_ktp' => $this->editRtRwKtp,
            'kel_desa_ktp' => $this->editKelDesaKtp,
            'kecamatan_ktp' => $this->editKecamatanKtp,
            'no_wa_hp' => $this->editNoWaHp,
            'pekerjaan' => $this->editPekerjaan,
        ]);

        Session::flash('success', 'Data Warga berhasil diperbarui.');
        $this->closeEditModal();
    }

    public function deleteWarga($id)
    {
        if (!in_array(Auth::user()?->role, ['admin', 'data'])) {
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

        Session::flash('success', 'Data Warga beserta fotonya berhasil dihapus.');
    }
}
