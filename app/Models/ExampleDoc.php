<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExampleDoc extends Model
{
    protected $table = 'tm_example_docs';

    protected $fillable = [
        'title',
        'file',
    ];

    // Query scope
    #[Scope]
    public function search(Builder $query, array $filters): void
    {
        $query->when($filters['id'] ?? null, function ($query, $id) {
            $Ids = is_array($id) ? $id : explode(',', $id);
            $query->whereIn('id', array_map('trim', $Ids));
        });

        $query->when($filters['title'] ?? null, fn ($query, $title) =>
            $query->where('title', 'like', "%{$title}%")
        );
    }
}
