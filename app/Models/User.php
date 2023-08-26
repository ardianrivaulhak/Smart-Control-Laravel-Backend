<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use SoftDeletes;
    use Uuids;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'npk',
        'name',
        'email',
        'password',
        'role_id',
        'stream_id',
        'photo_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function streams()
    {
        return $this->hasManyThrough(Stream::class, UserHasStream::class, 'user_id', 'id', 'id', 'stream_id');
    }

    public function stream_section_head()
    {
        # code...
        return $this->hasOne(StreamSectionHead::class, "user_id", "id");
    }

    public function stream_verifications()
    {
        # code...
        return $this->hasOne(StreamVerification::class, "user_id", "id");
    }

    public function cpi_orders()
    {
        return $this->hasMany(CpiOrder::class, "user_id", "id");
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, "user_id", "id");
    }
}
