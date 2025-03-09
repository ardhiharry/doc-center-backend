<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'project_name' => $this->project_name,
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'director_name' => $this->director_name,
            'director_phone' => $this->director_phone,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];
    }
}
