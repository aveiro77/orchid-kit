<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Event extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable;

    protected $fillable = [
        'judul',
        'deskripsi',
        'lokasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'kuota',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'kuota' => 'integer',
    ];

    /**
     * Allowed sort fields in Orchid tables.
     */
    protected $allowedSorts = [
        'id',
        'judul',
        'lokasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'kuota',
        'status',
        'created_at',
    ];

    /**
     * Allowed filter fields in Orchid tables.
     */
    protected $allowedFilters = [
        'id'     => Where::class,
        'judul'  => Like::class,
        'lokasi' => Like::class,
        'status' => Where::class,
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_registrations')
            ->withPivot('checkin_at')
            ->withTimestamps();
    }
}
