<?php

namespace App\Helpers;

use Carbon\Carbon;
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
}
