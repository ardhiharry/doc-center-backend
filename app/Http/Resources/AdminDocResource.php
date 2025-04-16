<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDocResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'file' => $this->file ? '/storage/'.$this->file : '',
            'project_id' => optional($this->project)->id,
            'project_name' => optional($this->project)->name,
            'admin_doc_category_id' => optional($this->adminDocCategory)->id,
            'admin_doc_category_name' => optional($this->adminDocCategory)->name,
            'created_at' => $this->created_at
        ];
    }
}
