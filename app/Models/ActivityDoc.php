<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityDoc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tp_5_activity_docs';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'title',
        'files',
        'description',
        'tags',
        'activity_id'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'tags' => 'array',
        'files' => 'array'
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }
}
