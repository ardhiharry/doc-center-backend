<?php

namespace App\Http\Controllers;

use App\Helpers\File;
use App\Helpers\Response;
use Illuminate\Http\Request;

class UploadChunkController extends Controller
{
    public function create(Request $request)
    {
        try {
            $uploadId = $request->input('upload_id');
            $chunkIndex = $request->input('chunk_index');
            $totalChunks = $request->input('total_chunks');
            $originalName = $request->input('original_name');

            $file = $request->file('file');

            if (
                !$file || is_null($uploadId) || is_null($chunkIndex)
                || is_null($totalChunks) || is_null($originalName)
            ) {
                return Response::handler(400, 'Bad Request', [], [], ['file' => ['Missing upload information.']]);
            }

            File::storeChunk($file, $chunkIndex, $uploadId);

            if ((int) $chunkIndex+1 === (int) $totalChunks) {
                $storedPath = File::mergeChunks($uploadId, $originalName, 'admin_docs');

                return Response::handler(200, 'Upload complete', ['file' => $storedPath]);
            }

            return Response::handler(200, 'Chunk received', [
                'chunk' => $chunkIndex,
                'status' => 'waiting for more chunks'
            ]);
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal upload file',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function delete(Request $request)
    {
        try {
            $file = $request->input('file');
            $path = storage_path('app/public/' . $file);

            if (file_exists($path)) {
                unlink($path);
                return Response::handler(
                    200,
                    'File berhasil dihapus',
                );
            }

            return Response::handler(
                400,
                'File tidak ditemukan',
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus file',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
