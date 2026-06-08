<?php

namespace App\Livewire\Data;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Warga;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;

class WargaForm extends Component
{
    use WithFileUploads;

    public $nik, $nama, $tempat_tgl_lahir, $jenis_kelamin;
    public $alamat_ktp, $rt_rw_ktp, $kel_desa_ktp, $kecamatan_ktp;
    
    public $is_domisili_sesuai_ktp = true;
    
    // For manual domisili
    public $alamat_detail_domisili, $rt_rw_domisili, $kode_pos_domisili;
    
    // API Dropdown states
    public $provinces = [];
    public $cities = [];
    public $districts = [];
    public $villages = [];
    
    // Selected values (format: "id|name")
    public $selectedProvinsi = null;
    public $selectedKota = null;
    public $selectedKecamatan = null;
    public $selectedKelurahan = null;
    
    public $no_wa_hp, $pekerjaan;
    
    public $foto_ktp, $foto_wajah;
    
    public $successMessage = '';

    // Duplicate Check State
    public $existingWarga = null;
    public $showExistingWargaModal = false;

    protected $rules = [
        'nik' => 'required|string|size:16|unique:wargas,nik',
        'nama' => 'required|string|max:255',
        'alamat_ktp' => 'required|string',
        'no_wa_hp' => 'required|string',
        'pekerjaan' => 'required|string',
        'foto_ktp' => 'required|image|max:30720',
        'foto_wajah' => 'required|image|max:30720',
    ];

    public function updatedIsDomisiliSesuaiKtp($value)
    {
        if (!$value && empty($this->provinces)) {
            // Load provinces when unchecked
            try {
                $response = Http::get('https://ibnux.github.io/data-indonesia/provinsi.json');
                $this->provinces = $response->json() ?? [];
            } catch (\Exception $e) {
                // handle error silently or fallback
            }
        }
        
        if ($value) {
            // Reset manual inputs if checked again
            $this->selectedProvinsi = null;
            $this->selectedKota = null;
            $this->selectedKecamatan = null;
            $this->selectedKelurahan = null;
            $this->alamat_detail_domisili = null;
            $this->rt_rw_domisili = null;
            $this->kode_pos_domisili = null;
        }
    }

    public function updatedNik($value)
    {
        if (strlen($value) === 16) {
            $this->existingWarga = Warga::where('nik', $value)->first();
        } else {
            $this->existingWarga = null;
        }
    }

    public function viewExistingWarga()
    {
        if ($this->existingWarga) {
            $this->showExistingWargaModal = true;
        }
    }

    public function closeExistingWargaModal()
    {
        $this->showExistingWargaModal = false;
    }

    public function updatedSelectedProvinsi($value)
    {
        $this->cities = [];
        $this->districts = [];
        $this->villages = [];
        $this->selectedKota = null;
        $this->selectedKecamatan = null;
        $this->selectedKelurahan = null;

        if ($value) {
            $id = explode('|', $value)[0];
            try {
                $response = Http::get("https://ibnux.github.io/data-indonesia/kabupaten/{$id}.json");
                $this->cities = $response->json() ?? [];
            } catch (\Exception $e) {}
        }
    }

    public function updatedSelectedKota($value)
    {
        $this->districts = [];
        $this->villages = [];
        $this->selectedKecamatan = null;
        $this->selectedKelurahan = null;

        if ($value) {
            $id = explode('|', $value)[0];
            try {
                $response = Http::get("https://ibnux.github.io/data-indonesia/kecamatan/{$id}.json");
                $this->districts = $response->json() ?? [];
            } catch (\Exception $e) {}
        }
    }

    public function updatedSelectedKecamatan($value)
    {
        $this->villages = [];
        $this->selectedKelurahan = null;

        if ($value) {
            $id = explode('|', $value)[0];
            try {
                $response = Http::get("https://ibnux.github.io/data-indonesia/kelurahan/{$id}.json");
                $this->villages = $response->json() ?? [];
            } catch (\Exception $e) {}
        }
    }

    public function submit(ImageService $imageService)
    {
        if ($this->existingWarga) {
            $this->addError('nik', 'Data dengan NIK ini sudah terdaftar.');
            return;
        }

        $this->validate();

        // Validate domisili manually if false
        if (!$this->is_domisili_sesuai_ktp) {
            $this->validate([
                'selectedProvinsi' => 'required',
                'selectedKota' => 'required',
                'selectedKecamatan' => 'required',
                'selectedKelurahan' => 'required',
                'alamat_detail_domisili' => 'required|string',
            ], [
                'selectedProvinsi.required' => 'Provinsi wajib dipilih.',
                'selectedKota.required' => 'Kota/Kabupaten wajib dipilih.',
                'selectedKecamatan.required' => 'Kecamatan wajib dipilih.',
                'selectedKelurahan.required' => 'Kelurahan wajib dipilih.',
                'alamat_detail_domisili.required' => 'Detail alamat domisili wajib diisi.',
            ]);
        }

        try {
            DB::beginTransaction();

            $fotoKtpPath = $imageService->compressAndSaveSecurely($this->foto_ktp, 'ktp');
            $fotoWajahPath = $imageService->compressAndSaveSecurely($this->foto_wajah, 'wajah');

            // Determine domisili data
            if ($this->is_domisili_sesuai_ktp) {
                // If same as KTP, we just leave them null or copy them. We leave them null since the flag `is_domisili_sesuai_ktp` indicates it.
                // Or we can copy the values. Let's just set the flag to true and null out the specific fields.
                $provinsi = null;
                $kota = null;
                $kecamatan = $this->kecamatan_ktp; // Not copying full hierarchy, just rely on the boolean in views
                $kelurahan = $this->kel_desa_ktp;
                $alamatDetail = $this->alamat_ktp . ($this->rt_rw_ktp ? ' RT/RW ' . $this->rt_rw_ktp : '');
                $kodePos = null;
            } else {
                // Extract names from format "id|name"
                $provinsi = $this->selectedProvinsi ? explode('|', $this->selectedProvinsi)[1] : null;
                $kota = $this->selectedKota ? explode('|', $this->selectedKota)[1] : null;
                $kecamatan = $this->selectedKecamatan ? explode('|', $this->selectedKecamatan)[1] : null;
                $kelurahan = $this->selectedKelurahan ? explode('|', $this->selectedKelurahan)[1] : null;
                
                // Combine manual fields
                $alamatDetail = $this->alamat_detail_domisili;
                if ($this->rt_rw_domisili) {
                    $alamatDetail .= ' RT/RW ' . $this->rt_rw_domisili;
                }
                $kodePos = $this->kode_pos_domisili;
            }

            Warga::create([
                'nik' => $this->nik,
                'nama' => $this->nama,
                'tempat_tgl_lahir' => $this->tempat_tgl_lahir,
                'jenis_kelamin' => $this->jenis_kelamin,
                'alamat_ktp' => $this->alamat_ktp,
                'rt_rw_ktp' => $this->rt_rw_ktp,
                'kel_desa_ktp' => $this->kel_desa_ktp,
                'kecamatan_ktp' => $this->kecamatan_ktp,
                'is_domisili_sesuai_ktp' => $this->is_domisili_sesuai_ktp,
                
                'provinsi_domisili' => $provinsi,
                'kota_kab_domisili' => $kota,
                'kecamatan_domisili' => $kecamatan,
                'kel_desa_domisili' => $kelurahan,
                'alamat_detail_domisili' => $alamatDetail,
                'kode_pos_domisili' => $kodePos,
                
                'no_wa_hp' => $this->no_wa_hp,
                'pekerjaan' => $this->pekerjaan,
                'foto_ktp_path' => $fotoKtpPath,
                'foto_wajah_path' => $fotoWajahPath,
            ]);

            DB::commit();

            $this->reset();
            // Reset state
            $this->existingWarga = null;
            $this->showExistingWargaModal = false;
            
            $this->successMessage = 'Data warga berhasil didaftarkan.';
            $this->dispatch('warga-saved');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.data.warga-form');
    }
}
