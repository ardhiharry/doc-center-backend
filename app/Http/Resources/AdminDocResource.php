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
            'file' => $this->file,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'admin_doc_category' => new AdminDocCategoryResource($this->whenLoaded('adminDocCategory')),
            'created_at' => $this->created_at
        ];
    }
}
