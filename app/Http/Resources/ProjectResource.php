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
            'name' => $this->name,
            'company_id' => optional($this->company)->id,
            'company_name' => optional($this->company)->name,
            'company_address' => optional($this->company)->address,
            'company_director_name' => optional($this->company)->director_name,
            'company_director_phone' => optional($this->company)->director_phone,
            'company_director_signature' => optional($this->company)->director_signature,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
