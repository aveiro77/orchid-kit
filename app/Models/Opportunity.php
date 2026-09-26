<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Opportunity extends Model
{
    use AsSource, Attachable, Filterable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'tipe',
        'judul',
        'deskripsi',
        'lokasi',
        'tanggal_expired',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_expired' => 'date',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id' => Where::class,
        'user_id' => Where::class,
        'tipe' => Where::class,
        'judul' => Like::class,
        'lokasi' => Like::class,
        'status' => Where::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'tipe',
        'judul',
        'lokasi',
        'tanggal_expired',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * Owner of the opportunity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
