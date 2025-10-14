<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Upload media file (image, audio, or video) for questions
     */
    public function upload(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp3,mp4,wav,ogg,webm,avi|max:10240', // max 10MB
        ]);

        try {
            $file = $request->file('media');
            $mimeType = $file->getMimeType();
            
            // Determine media type based on MIME type
            $mediaType = 'file';
            if (str_starts_with($mimeType, 'image/')) {
                $mediaType = 'image';
            } elseif (str_starts_with($mimeType, 'audio/')) {
                $mediaType = 'audio';
            } elseif (str_starts_with($mimeType, 'video/')) {
                $mediaType = 'video';
            }

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $extension;
            
            // Store file in public disk under 'question-media' directory
            $path = $file->storeAs('question-media', $filename, 'public');
            
            // Generate public URL
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'type' => $mediaType,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
