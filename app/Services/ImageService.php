<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Compress and save the image to the secure storage.
     * Target size is under 300KB.
     *
     * @param UploadedFile|string $file Uploaded file or base64 string
     * @param string $folder Target folder inside secure_ktp
     * @return string The generated file path
     */
    public function compressAndSaveSecurely($file, string $folder = ''): string
    {
        $filename = Str::uuid() . '.jpg';
        $path = "secure_ktp/{$folder}/{$filename}";

        if ($file instanceof UploadedFile) {
            $imagePath = $file->getRealPath();
        } else {
            // Assume base64 string (Data URI)
            $imageParts = explode(';base64,', $file);
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'jpeg';
            $imageBase64 = base64_decode($imageParts[1] ?? $imageParts[0]);
            
            $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $imageType;
            file_put_contents($tempPath, $imageBase64);
            $imagePath = $tempPath;
        }

        // Get original dimensions
        list($origWidth, $origHeight, $type) = getimagesize($imagePath);

        // Check EXIF orientation for JPEG to fix rotation issues
        $deg = 0;
        if ($type == IMAGETYPE_JPEG) {
            $exif = @exif_read_data($imagePath);
            if ($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                switch ($orientation) {
                    case 3:
                        $deg = 180;
                        break;
                    case 6:
                        $deg = 270; // 90 CW
                        break;
                    case 8:
                        $deg = 90; // 90 CCW
                        break;
                }
            }
        }

        // Swap dimensions if rotated 90 or 270 degrees
        if ($deg == 90 || $deg == 270) {
            $temp = $origWidth;
            $origWidth = $origHeight;
            $origHeight = $temp;
        }

        // Calculate new dimensions (max 1200px width/height)
        $maxWidth = 1200;
        $maxHeight = 1200;
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);

        // Skip processing if image is already small enough, valid JPEG, and not rotated
        if ($ratio >= 1 && $deg == 0 && $type == IMAGETYPE_JPEG && filesize($imagePath) <= 300000) {
            Storage::disk('local')->put($path, file_get_contents($imagePath));
            if (!$file instanceof UploadedFile) {
                unlink($imagePath);
            }
            return $path;
        }

        if ($ratio < 1) {
            $newWidth = $origWidth * $ratio;
            $newHeight = $origHeight * $ratio;
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Load original image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($imagePath);
                if ($deg) {
                    $sourceImage = imagerotate($sourceImage, $deg, 0);
                }
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($imagePath);
                // Handle transparency for PNG
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($imagePath);
                break;
            default:
                throw new \Exception('Unsupported image type.');
        }

        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save to temporary file with compression
        $tempOutput = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
        
        // Start with 75% quality for a good balance of size and visual
        $quality = 75;
        imagejpeg($newImage, $tempOutput, $quality);
        
        $size = filesize($tempOutput);
        if ($size > 300000) {
            // Drop quality based on size to avoid multiple expensive disk writes
            if ($size > 1500000) {
                $quality = 30;
            } elseif ($size > 800000) {
                $quality = 40;
            } else {
                $quality = 50;
            }
            imagejpeg($newImage, $tempOutput, $quality);
        }

        // Save to secure storage
        Storage::disk('local')->put($path, file_get_contents($tempOutput));

        // Clean up
        imagedestroy($newImage);
        imagedestroy($sourceImage);
        unlink($tempOutput);
        if (!$file instanceof UploadedFile) {
            unlink($imagePath);
        }

        return $path;
    }
}
