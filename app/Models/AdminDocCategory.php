<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminDocCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'admin_doc_categories';
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

    public function adminDocs(): HasMany
    {
        return $this->hasMany(AdminDoc::class, 'admin_doc_category_id', 'id');
    }
}
