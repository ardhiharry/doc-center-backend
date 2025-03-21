<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'address' => $this->address,
            'director_name' => $this->director_name,
            'director_phone' => $this->director_phone,
            'director_signature' => '/storage/'.$this->director_signature,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
