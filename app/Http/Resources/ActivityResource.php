<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'activity_category_id' => optional($this->activityCategory)->id,
            'activity_category_name' => optional($this->activityCategory)->name,
            'project_id' => optional($this->project)->id,
            'project_name' => optional($this->project)->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
