<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $hidden = [
        'deleted_at'
    ];

    protected $fillable = [
        'name',
        'company_id',
        'start_date',
        'end_date'
    ];

    public function adminDocs(): HasMany
    {
        return $this->hasMany(AdminDoc::class, 'project_id', 'id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'project_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
