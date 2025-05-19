<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'files' => is_array($this->files)
                ? array_map(function ($file) {
                    $path = $file;
                    $sizeBytes = Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : 0;

                    if ($sizeBytes >= 1000000) {
                        $size = round($sizeBytes / 1_000_000, 2) . ' MB';
                    } else {
                        $size = round($sizeBytes / 1_000, 2) . ' KB';
                    }

                    return [
                        'url' => '/storage/' . $file,
                        'size' => $size,
                    ];
                }, $this->files)
                : [],
            'description' => $this->description,
            'tags' => $this->tags,
            'activity_id' => optional($this->activity)->id,
            'activity_title' => optional($this->activity)->title,
            'project_id' => optional(optional($this->activity)->project)->id,
            'project_name' => optional(optional($this->activity)->project)->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
