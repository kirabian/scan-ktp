<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warga extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nik',
        'nama',
        'tempat_tgl_lahir',
        'jenis_kelamin',
        'alamat_ktp',
        'rt_rw_ktp',
        'kel_desa_ktp',
        'kecamatan_ktp',
        'is_domisili_sesuai_ktp',
        'provinsi_domisili',
        'kota_kab_domisili',
        'kecamatan_domisili',
        'kel_desa_domisili',
        'alamat_detail_domisili',
        'kode_pos_domisili',
        'no_wa_hp',
        'pekerjaan',
        'foto_ktp_path',
        'foto_wajah_path',
        'created_by_user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_domisili_sesuai_ktp' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function historiSedekahs()
    {
        return $this->hasMany(HistoriSedekah::class);
    }

    /**
     * Get the computed Umur based on tempat_tgl_lahir.
     */
    public function getUmurAttribute()
    {
        if (!$this->tempat_tgl_lahir) return '-';

        preg_match('/(\d{2})[- \/.](\d{2})[- \/.](\d{4})/', $this->tempat_tgl_lahir, $matches);
        
        if (count($matches) == 4) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            try {
                $date = \Carbon\Carbon::createFromDate($year, $month, $day);
                return $date->age;
            } catch (\Exception $e) {
                return '-';
            }
        }
        
        return '-';
    }
}
