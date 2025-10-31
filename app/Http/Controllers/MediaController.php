<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $this->validateUpload($request);

        try {
            $file = $request->file('media');

            $mediaType = $this->detectMediaType($file->getMimeType());
            $filename = $this->generateFilename($file->getClientOriginalExtension());

            $path = $file->storeAs('question-media', $filename, 'public');
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'type' => $mediaType,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function validateUpload(Request $request): void
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp3,mp4,wav,ogg,webm,avi|max:10240',
        ]);
    }

    private function detectMediaType(string $mimeType): string
    {
        $type = 'file';

        if (str_starts_with($mimeType, 'image/')) {
            $type = 'image';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            $type = 'audio';
        } elseif (str_starts_with($mimeType, 'video/')) {
            $type = 'video';
        }

        return $type;
    }

    private function generateFilename(string $extension): string
    {
        return Str::random(40) . '.' . ltrim($extension, '.');
    }
}

