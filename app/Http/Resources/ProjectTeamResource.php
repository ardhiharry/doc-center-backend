<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTeamResource extends JsonResource
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
            'project_id' => optional($this->project)->id,
            'project_name' => optional($this->project)->name,
            'user_id' => optional($this->user)->id,
            'user_username' => optional($this->user)->username,
            'user_name' => optional($this->user)->name,
            'user_is_process' => optional($this->user)->is_process
        ];
    }
}
