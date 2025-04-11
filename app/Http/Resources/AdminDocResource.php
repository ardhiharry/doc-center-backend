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
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name
                ];
            }),
            'admin_doc_category' => $this->whenLoaded('adminDocCategory', function () {
                return [
                    'id' => $this->adminDocCategory->id,
                    'name' => $this->adminDocCategory->name
                ];
            }),
            'created_at' => $this->created_at
        ];
    }
}
