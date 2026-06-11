<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin \Illuminate\Database\Query\Builder
 * @method static int count()
 * @method static \Illuminate\Database\Eloquent\Builder|static where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDate(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static whereNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static selectRaw(string $expression, array $bindings = [])
 * @method static \Illuminate\Database\Eloquent\Builder|static with(string|array $relations)
 * @method static static find(mixed $id, array $columns = ['*'])
 */
class HistoriSedekah extends Model
{
    protected $fillable = [
        'event_id',
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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
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
