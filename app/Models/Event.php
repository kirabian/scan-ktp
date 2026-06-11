<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @mixin \Illuminate\Database\Query\Builder
 * @method static int count()
 * @method static \Illuminate\Database\Eloquent\Builder|static where(string|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static currentlyActive()
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDate(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static whereNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static selectRaw(string $expression, array $bindings = [])
 * @method static \Illuminate\Database\Eloquent\Builder|static with(string|array $relations)
 * @method static static find(mixed $id, array $columns = ['*'])
 */
class Event extends Model
{
    protected $fillable = [
        'judul',
        'deskripsi',
        'tanggal_mulai',
        'jam_mulai',
        'tanggal_selesai',
        'jam_selesai',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function historiSedekahs(): HasMany
    {
        return $this->hasMany(HistoriSedekah::class);
    }

    /**
     * Cek apakah event sedang berlangsung saat ini.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) return false;

        $now = Carbon::now();
        $start = Carbon::parse($this->tanggal_mulai->format('Y-m-d') . ' ' . $this->jam_mulai);
        $end = Carbon::parse($this->tanggal_selesai->format('Y-m-d') . ' ' . $this->jam_selesai);

        return $now->between($start, $end);
    }

    /**
     * Scope untuk event yang sedang aktif (berlangsung saat ini).
     */
    public function scopeCurrentlyActive($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->where('tanggal_mulai', '<', $now->toDateString())
                    ->orWhere(function ($q2) use ($now) {
                        $q2->whereDate('tanggal_mulai', $now->toDateString())
                           ->whereTime('jam_mulai', '<=', $now->toTimeString());
                    });
            })
            ->where(function ($q) use ($now) {
                $q->where('tanggal_selesai', '>', $now->toDateString())
                    ->orWhere(function ($q2) use ($now) {
                        $q2->whereDate('tanggal_selesai', $now->toDateString())
                           ->whereTime('jam_selesai', '>=', $now->toTimeString());
                    });
            });
    }

    /**
     * Get formatted date range string.
     */
    public function getPeriodAttribute(): string
    {
        $start = $this->tanggal_mulai->format('d M Y') . ' ' . substr($this->jam_mulai, 0, 5);
        $end = $this->tanggal_selesai->format('d M Y') . ' ' . substr($this->jam_selesai, 0, 5);
        return $start . ' - ' . $end;
    }
}
