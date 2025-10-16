<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Media upload controller (images, audio, video). */
class MediaController extends Controller
{
    /** Upload media file (validates, stores, returns JSON metadata). */
    public function upload(Request $request)
    {
        $this->validateUpload($request);

        try {
            /** @var UploadedFile $file */
            $file = $request->file('media');

            $mediaType = $this->detectMediaType($file->getMimeType());

            $filename = $this->generateFilename($file->getClientOriginalExtension());

            // Store file under public/question-media
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
            // Log could be added here for real-world debugging
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function validateUpload(Request $request): void
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp3,mp4,wav,ogg,webm,avi|max:10240', // max 10MB
        ]);
    }

    private function detectMediaType(string $mimeType): string
    {
        // Use a simple match map to return a single expression (satisfies static analyzers)
        $type = 'file';
    if (str_starts_with($mimeType, 'image/')) { $type = 'image'; }
    elseif (str_starts_with($mimeType, 'audio/')) { $type = 'audio'; }
    elseif (str_starts_with($mimeType, 'video/')) { $type = 'video'; }

        return $type;
    }

    private function generateFilename(string $extension): string
    {
        return Str::random(40) . '.' . ltrim($extension, '.');
    }
}

