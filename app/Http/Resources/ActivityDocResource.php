<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityDocResource extends JsonResource
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
            'file' => '/storage/'.$this->file,
            'description' => $this->description,
            'tags' => $this->tags,
            'activity_doc_category' => new ActivityDocCategoryResource($this->whenLoaded('activityDocCategory')),
            'activity' => new ActivityResource($this->whenLoaded('activity')),
            'created_at' => $this->created_at
        ];
    }
}
