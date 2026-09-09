<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use UsesUuid;

    protected $fillable = [
        'product_id',
        'company_id',
        'uploaded_by',
        'original_path',
        'webp_path',
        'thumbnail_path',
        'status',
        'sort_order',
    ];

    protected $appends = ['preview_url'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getPreviewUrlAttribute(): ?string
    {
        $disk = Storage::disk('public');
        $path = collect([
            $this->thumbnail_path,
            $this->webp_path,
            $this->original_path,
        ])->first(fn (?string $candidate) => $candidate && $disk->exists($candidate));

        if (! $path) {
            return null;
        }

        $url = $disk->url($path);
        $relativeUrl = parse_url($url, PHP_URL_PATH);

        return is_string($relativeUrl) ? $relativeUrl : $url;
    }
}
