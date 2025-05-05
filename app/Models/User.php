<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes;

    protected $table = 'tm_users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = ['username', 'password', 'name', 'role', 'token', 'is_process'];

    protected $casts = [
        'role' => 'string'
    ];

    protected $hidden = ['password', 'token', 'deleted_at'];

    // JWT Auth
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'teams', 'user_id', 'project_id');
    }

    public function ledProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_leader_id', 'id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'author_id', 'id');
    }
}
