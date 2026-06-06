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
        $path = "secure_ktp/{$folder}/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $file = Storage::disk('local')->get($path);
        $type = Storage::disk('local')->mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    }
}
