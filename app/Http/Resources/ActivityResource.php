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
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'activity_category_id' => optional($this->activityCategory)->id,
            'activity_category_name' => optional($this->activityCategory)->name,
            'project_id' => optional($this->project)->id,
            'project_name' => optional($this->project)->name,
            'author_id' => optional($this->author)->id,
            'author_name' => optional($this->author)->name,
            'activity_doc' => !is_null($this->activityDoc) ? true : false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
