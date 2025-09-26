<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Kid;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KidPhotoController extends BaseController
{
    public function show(Kid $kid, string $filename): BinaryFileResponse|Response
    {
        $this->authorize('view', $kid);

        try {
            $filePath = "{$kid->id}/{$filename}";

            if (!Storage::disk('kids_photos')->exists($filePath)) {
                Log::warning('Kid photo not found', [
                    'kid_id' => $kid->id,
                    'filename' => $filename,
                    'user_id' => Auth::id(),
                ]);

                return $this->getDefaultPhoto();
            }

            $fullPath = Storage::disk('kids_photos')->path($filePath);
            $mimeType = Storage::disk('kids_photos')->mimeType($filePath);

            if (!$this->isValidImageType($mimeType)) {
                Log::warning('Invalid image type requested', [
                    'kid_id' => $kid->id,
                    'filename' => $filename,
                    'mime_type' => $mimeType,
                    'user_id' => Auth::id(),
                ]);

                return $this->getDefaultPhoto();
            }

            Log::info('Kid photo served', [
                'kid_id' => $kid->id,
                'filename' => $filename,
                'user_id' => Auth::id(),
            ]);

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]);

        } catch (\Exception $e) {
            Log::error('Error serving kid photo', [
                'kid_id' => $kid->id,
                'filename' => $filename,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return $this->getDefaultPhoto();
        }
    }

    private function isValidImageType(?string $mimeType): bool
    {
        $validMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
        ];

        return $mimeType && in_array($mimeType, $validMimeTypes, true);
    }

    private function getDefaultPhoto(): BinaryFileResponse
    {
        $defaultPhotoPath = public_path('images/kids/default.png');

        if (!file_exists($defaultPhotoPath)) {
            $defaultPhotoPath = public_path('images/avatar-default.png');
        }

        return response()->file($defaultPhotoPath, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}