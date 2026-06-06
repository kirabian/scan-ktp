<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriSedekah extends Model
{
    protected $fillable = [
        'warga_id',
        'petugas_security_id',
        'waktu_ambil',
        'foto_penerima_path',
    ];

    protected function casts(): array
    {
        return [
            'waktu_ambil' => 'datetime',
        ];
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }

    public function petugasSecurity(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_security_id');
    }
}
