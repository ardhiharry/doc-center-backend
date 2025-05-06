<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tp_4_activities';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'activity_category_id',
        'project_id',
        'author_id',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($activity) {
            $activity->activityDoc()->delete();
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tr_activity_teams', 'activity_id', 'user_id');
    }

    public function activityCategory(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class, 'activity_category_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function activityDoc(): HasOne
    {
        return $this->hasOne(ActivityDoc::class, 'activity_id', 'id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }
}
