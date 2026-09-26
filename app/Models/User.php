<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes, Attachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'foto',
        'nomor_wa',
        'kota',
        'bio',
        'linkedin',
        'website',
        'instagram',
        'status_aktif',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
        'status_aktif'         => 'boolean',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'           => Where::class,
        'name'         => Like::class,
        'email'        => Like::class,
        'kota'         => Like::class,
        'status_aktif' => Where::class,
        'updated_at'   => WhereDateStartEnd::class,
        'created_at'   => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'kota',
        'status_aktif',
        'updated_at',
        'created_at',
    ];

    /**
     * Professional roles associated with the user.
     */
    public function professionalRoles(): BelongsToMany
    {
        return $this->belongsToMany(ProfessionalRole::class, 'professional_role_user')->withTimestamps();
    }

    /**
     * Skills associated with the user.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'skill_user')->withTimestamps();
    }

    /**
     * Businesses owned by the user.
     */
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    /**
     * Opportunities created by the user.
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    /**
     * Referrals given by the user.
     */
    public function referralsGiven(): HasMany
    {
        return $this->hasMany(Referral::class, 'pemberi_referral_id');
    }

    /**
     * Referrals received by the user.
     */
    public function referralsReceived(): HasMany
    {
        return $this->hasMany(Referral::class, 'penerima_referral_id');
    }

    /**
     * Event registrations made by the user.
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Events attended or registered by the user.
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_registrations')
            ->withPivot('checkin_at')
            ->withTimestamps();
    }
}
