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
            'activity_doc_category' => $this->whenLoaded('activityDocCategory', function () {
                return [
                    'id' => $this->activityDocCategory->id,
                    'name' => $this->activityDocCategory->name
                ];
            }),
            'activity' => $this->whenLoaded('activity', function () {
                return [
                    'id' => $this->activity->id,
                    'title' => $this->activity->title,
                    'project' => [
                        'id' => $this->activity->project->id,
                        'name' => $this->activity->project->name
                    ]
                ];
            }),
            'created_at' => $this->created_at
        ];
    }
}
