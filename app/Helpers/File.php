<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class File
{
    public static function generate($file, $directory): array
    {
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->translatedFormat('m');

        $uuid = Str::uuid()->toString();
        $randomStr = substr(str_replace('-', '', $uuid), 0, 7);
        $originalName = ucwords(strtolower(str_replace('_', ' ', $file->getClientOriginalName())));
        $fileName = "{$now->format('Ymd')}-{$randomStr}-{$originalName}";

        $path = "{$directory}/{$year}/{$month}";

        return [
            'path' => $path,
            'fileName' => $fileName,
            'fullPath' => "{$path}/{$fileName}"
        ];
    }

    public static function storeChunk($file, $chunkIndex, $uploadId): string
    {
        $chunkPath = storage_path("app/public/chunks/temp/{$uploadId}");

        if (!file_exists($chunkPath)) {
            mkdir($chunkPath, 0755, true);
        }

        $fileName = "{$chunkPath}/chunk{$chunkIndex}";
        file_put_contents($fileName, file_get_contents($file->getRealPath()));

        return $fileName;
    }

    public static function mergeChunks($uploadId, $file, $directory): string
    {
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->translatedFormat('m');

        $uuid = Str::uuid()->toString();
        $randomStr = substr(str_replace('-', '', $uuid), 0, 7);
        $fileName = "{$now->format('Ymd')}-{$randomStr}-" . ucwords(strtolower(str_replace('_', ' ', $file)));
        $path = "public/{$directory}/{$year}/{$month}";
        $fullPath = storage_path("app/{$path}");

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $finalPath = "{$fullPath}/{$fileName}";
        $chunkPath = storage_path("app/public/chunks/temp/{$uploadId}");

        $filesystem = new Filesystem();
        $chunks = collect($filesystem->files($chunkPath))
            ->sortBy(function ($chunk) {
                return intval(str_replace('chunk', '', $chunk->getFilename()));
            });

        $finalFile = fopen($finalPath, 'ab');

        foreach ($chunks as $chunk) {
            $data = file_get_contents($chunk->getRealPath());
            fwrite($finalFile, $data);
        }

        fclose($finalFile);

        Filesystem::deleteDirectory($chunkPath);

        return str_replace("public/", "", "{$path}/{$fileName}");
    }
}
