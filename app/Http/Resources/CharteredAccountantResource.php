<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharteredAccountantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'application_date' => $this->application_date,
            'classification' => $this->classification,
            'total' => $this->total,
            'description' => $this->description,
            'images' => $this->images,
            'applicant_name' => optional($this->applicant)->username,
            'project_name' => optional($this->project)->name
        ];
    }
}
