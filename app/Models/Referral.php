<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Referral extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pemberi_referral_id',
        'penerima_referral_id',
        'client_name',
        'project_name',
        'nilai_estimasi',
        'status',
        'catatan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'nilai_estimasi' => 'decimal:2',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'                   => Where::class,
        'pemberi_referral_id'  => Where::class,
        'penerima_referral_id' => Where::class,
        'client_name'          => Like::class,
        'project_name'         => Like::class,
        'status'               => Where::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'pemberi_referral_id',
        'penerima_referral_id',
        'client_name',
        'project_name',
        'nilai_estimasi',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * Pemberi referral (User).
     */
    public function pemberi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemberi_referral_id');
    }

    /**
     * Penerima referral (User).
     */
    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_referral_id');
    }
}
