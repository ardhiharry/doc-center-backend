<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'activity_categories';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function activity(): HasMany
    {
        return $this->hasMany(Activity::class, 'activity_category_id', 'id');
    }
}
