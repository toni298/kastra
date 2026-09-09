<?php

namespace App\Jobs;

use App\Models\ProductImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $imageId) {}

    public function handle(): void
    {
        $image = ProductImage::findOrFail($this->imageId);
        $image->update(['status' => 'processing']);

        try {
            $source = Storage::disk('public')->path($image->original_path);
            $sourceData = file_get_contents($source);

            // Suppress GD warnings (e.g. libpng incorrect sRGB profile / iCCP warnings).
            // The image still decodes successfully; we just silence the noisy warnings.
            $resource = @imagecreatefromstring($sourceData);

            if (!$resource) {
                $image->update(['status' => 'failed']);
                return;
            }

            $directory = "products/{$image->company_id}/processed";
            $name = Str::uuid();
            $webpPath = "$directory/$name.webp";
            $thumbnailPath = "$directory/{$name}_thumb.webp";

            Storage::disk('public')->makeDirectory($directory);

            imagewebp($resource, Storage::disk('public')->path($webpPath), 82);

            $width = imagesx($resource);
            $height = imagesy($resource);
            $thumbWidth = 320;
            $thumbHeight = max(1, (int) round($height * ($thumbWidth / $width)));
            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagecopyresampled($thumb, $resource, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
            imagewebp($thumb, Storage::disk('public')->path($thumbnailPath), 78);

            imagedestroy($thumb);
            imagedestroy($resource);

            $image->update([
                'webp_path' => $webpPath,
                'thumbnail_path' => $thumbnailPath,
                'status' => 'ready',
            ]);
        } catch (\Throwable $e) {
            $image->update(['status' => 'failed']);
            throw $e;
        }
    }
}
