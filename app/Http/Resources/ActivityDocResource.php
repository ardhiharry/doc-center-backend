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
                    $size = Storage::exists($path) ? Storage::size($path) : 0;
                    $sizeKB = round($size / 1000, 2);
                    $sizeMB = round($size / 1000000, 2);

                    return [
                        'url' => '/storage/' . $file,
                        'size' => $size < 1000000 ? $sizeKB . ' KB' : $sizeMB . ' MB',
                    ];
                }, $this->files)
                : [],
            'description' => $this->description,
            'tags' => $this->tags,
            'activity_id' => optional($this->activity)->id,
            'activity_title' => optional($this->activity)->title,
            'project_id' => optional(optional($this->activity)->project)->id,
            'project_name' => optional(optional($this->activity)->project)->name,
            'created_at' => $this->created_at
        ];
    }
}
