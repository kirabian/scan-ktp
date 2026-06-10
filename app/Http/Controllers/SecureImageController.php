<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SecureImageController extends Controller
{
    /**
     * Serve a secure image to authenticated users.
     */
    public function show($folder, $filename)
    {
        // Release session lock immediately to prevent blocking subsequent parallel requests
        $session = session();
        if ($session !== null && $session->isStarted()) {
            $session->save();
        }

        $path = "secure_ktp/{$folder}/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        // Stream file response directly for memory efficiency and caching
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
