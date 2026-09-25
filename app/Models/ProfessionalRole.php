<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class ProfessionalRole extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'nama',
    ];

    protected $allowedFilters = [
        'id'   => Where::class,
        'nama' => Like::class,
    ];

    protected $allowedSorts = [
        'id',
        'nama',
        'created_at',
        'updated_at',
    ];

    /**
     * Users associated with the professional role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'professional_role_user')->withTimestamps();
    }
}
