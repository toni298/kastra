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
            $disk = Storage::disk('public');
            $sourceData = $disk->get($image->original_path);

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

            $webpFile = tempnam(sys_get_temp_dir(), 'kastra-webp-');
            $thumbnailFile = tempnam(sys_get_temp_dir(), 'kastra-thumb-');
            imagewebp($resource, $webpFile, 82);

            $width = imagesx($resource);
            $height = imagesy($resource);
            $thumbWidth = 320;
            $thumbHeight = max(1, (int) round($height * ($thumbWidth / $width)));
            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagecopyresampled($thumb, $resource, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
            imagewebp($thumb, $thumbnailFile, 78);

            $disk->put($webpPath, file_get_contents($webpFile), ['visibility' => 'public']);
            $disk->put($thumbnailPath, file_get_contents($thumbnailFile), ['visibility' => 'public']);

            @unlink($webpFile);
            @unlink($thumbnailFile);

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
