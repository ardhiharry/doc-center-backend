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
            'file' => $this->file ? '/storage/'.$this->file : '',
            'description' => $this->description,
            'tags' => $this->tags,
            'activity_doc_category_id' => optional($this->activityDocCategory)->id,
            'activity_doc_category_name' => optional($this->activityDocCategory)->name,
            'activity_id' => optional($this->activity)->id,
            'activity_title' => optional($this->activity)->title,
            'project_id' => optional(optional($this->activity)->project)->id,
            'project_name' => optional(optional($this->activity)->project)->name,
            'created_at' => $this->created_at
        ];
    }
}
