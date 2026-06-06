<?php

namespace App\Livewire\Data;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Warga;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;

class WargaForm extends Component
{
    use WithFileUploads;

    public $nik, $nama, $tempat_tgl_lahir, $jenis_kelamin;
    public $alamat_ktp, $rt_rw_ktp, $kel_desa_ktp, $kecamatan_ktp;
    
    public $is_domisili_sesuai_ktp = true;
    public $provinsi_domisili, $kota_kab_domisili, $kecamatan_domisili, $kel_desa_domisili, $alamat_detail_domisili, $kode_pos_domisili;
    
    public $no_wa_hp, $pekerjaan;
    
    public $foto_ktp, $foto_wajah;
    
    public $successMessage = '';

    protected $rules = [
        'nik' => 'required|string|size:16|unique:wargas,nik',
        'nama' => 'required|string|max:255',
        'alamat_ktp' => 'required|string',
        'no_wa_hp' => 'required|string',
        'pekerjaan' => 'required|string',
        'foto_ktp' => 'required|image|max:10240', // Validate size initially, then compress
        'foto_wajah' => 'required|image|max:10240',
    ];

    public function updatedIsDomisiliSesuaiKtp($value)
    {
        if ($value) {
            $this->provinsi_domisili = null;
            $this->kota_kab_domisili = null;
            $this->kecamatan_domisili = null;
            $this->kel_desa_domisili = null;
            $this->alamat_detail_domisili = null;
            $this->kode_pos_domisili = null;
        }
    }

    public function submit(ImageService $imageService)
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $fotoKtpPath = $imageService->compressAndSaveSecurely($this->foto_ktp, 'ktp');
            $fotoWajahPath = $imageService->compressAndSaveSecurely($this->foto_wajah, 'wajah');

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
                'provinsi_domisili' => $this->provinsi_domisili,
                'kota_kab_domisili' => $this->kota_kab_domisili,
                'kecamatan_domisili' => $this->kecamatan_domisili,
                'kel_desa_domisili' => $this->kel_desa_domisili,
                'alamat_detail_domisili' => $this->alamat_detail_domisili,
                'kode_pos_domisili' => $this->kode_pos_domisili,
                'no_wa_hp' => $this->no_wa_hp,
                'pekerjaan' => $this->pekerjaan,
                'foto_ktp_path' => $fotoKtpPath,
                'foto_wajah_path' => $fotoWajahPath,
            ]);

            DB::commit();

            $this->reset();
            $this->successMessage = 'Data warga berhasil didaftarkan.';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.data.warga-form')->layout('layouts.app');
    }
}
