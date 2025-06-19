<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharteredAccountant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tp_6_chartered_accountants';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $hidden = [
        'deleted_at'
    ];

    protected $fillable = [
        'application_date',
        'classification',
        'total',
        'description',
        'images',
        'applicant_id',
        'project_id'
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}
