<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminDoc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'admin_docs';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'title',
        'file',
        'date',
        'project_id',
        'admin_doc_category_id',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function adminDocCategory(): BelongsTo
    {
        return $this->belongsTo(AdminDocCategory::class, 'admin_doc_category_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}
