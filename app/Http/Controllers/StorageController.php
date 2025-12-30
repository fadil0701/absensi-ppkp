<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageController extends Controller
{
    /**
     * Serve storage files
     * This route is public and does not require authentication
     */
    public function serve(string $path): BinaryFileResponse|\Illuminate\Http\Response
    {
        // Prevent directory traversal attacks
        $path = str_replace('..', '', $path);
        $path = ltrim($path, '/');

        $filePath = storage_path('app/public/'.$path);

        // Normalize path to prevent directory traversal
        $normalizedPath = realpath($filePath);
        $storagePath = realpath(storage_path('app/public'));

        // Security check: ensure file is within storage/app/public directory
        if (! $normalizedPath || ! $storagePath || strpos($normalizedPath, $storagePath) !== 0) {
            \Log::warning('Storage file access denied - path traversal attempt or invalid path', [
                'requested_path' => $path,
                'file_path' => $filePath,
            ]);
            abort(404, 'File not found');
        }

        if (! file_exists($normalizedPath) || ! is_file($normalizedPath)) {
            \Log::warning('Storage file not found', [
                'requested_path' => $path,
                'normalized_path' => $normalizedPath,
            ]);
            abort(404, 'File not found');
        }

        // Get MIME type
        $mimeType = mime_content_type($normalizedPath);
        if (! $mimeType) {
            // Try to guess from extension
            $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        }

        return response()->file($normalizedPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
