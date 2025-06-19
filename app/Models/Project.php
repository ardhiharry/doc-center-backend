<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tp_1_projects';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $hidden = [
        'deleted_at'
    ];

    protected $fillable = [
        'name',
        'code',
        'contract_number',
        'contract_date',
        'client',
        'ppk',
        'support_teams',
        'value',
        'status',
        'progress',
        'company_id',
        'project_leader_id',
        'start_date',
        'end_date',
        'maintenance_date',
    ];

    protected $casts = [
        'status' => 'string',
        'progress' => 'integer',
        'support_teams' => 'array',
        'value' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($project) {
            $project->adminDocs()->delete();
            $project->activities->each->delete();
        });
    }

    public function adminDocs(): HasMany
    {
        return $this->hasMany(AdminDoc::class, 'project_id', 'id');
    }

    public function activityCategories(): HasMany
    {
        return $this->hasMany(ActivityCategory::class, 'project_id', 'id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'project_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tr_project_teams', 'project_id', 'user_id');
    }

    public function projectLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_leader_id', 'id');
    }

    public function charteredAccountants(): HasMany
    {
        return $this->hasMany(CharteredAccountant::class, 'project_id', 'id');
    }
}
