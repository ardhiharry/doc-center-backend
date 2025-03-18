<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $hidden = [
        'deleted_at'
    ];

    protected $fillable = [
        'name',
        'address',
        'director_name',
        'director_phone',
        'director_signature'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($company) {
            $company->projects->each->delete();
        });
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'company_id', 'id');
    }
}
