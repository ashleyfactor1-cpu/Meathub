<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producer extends Model
{
    protected $fillable = [
        'name',
        'location',
        'address',
        'map_embed_url',
        'contact_number',
        'bio',
        'avatar',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function mapEmbedSource(): ?string
    {
        if (self::isGoogleMapsEmbedUrl($this->map_embed_url)) {
            return $this->map_embed_url;
        }

        if (blank($this->address)) {
            return null;
        }

        return 'https://maps.google.com/maps?q='.urlencode($this->address).'&output=embed';
    }

    public static function isGoogleMapsEmbedUrl(?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        $parts = parse_url($url);

        return is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && in_array($parts['host'] ?? null, ['google.com', 'www.google.com', 'maps.google.com'], true)
            && preg_match('~^/maps(?:/|$)~', $parts['path'] ?? '') === 1;
    }
}
