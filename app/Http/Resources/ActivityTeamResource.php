<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityTeamResource extends JsonResource
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
            'activity_id' => optional($this->activity)->id,
            'activity_title' => optional($this->activity)->title,
            'user_id' => optional($this->user)->id,
            'user_name' => optional($this->user)->name
        ];
    }
}
