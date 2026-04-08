<?php

namespace App\Http\Resources;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RealisationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $media = $this->media ?? [];

        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'date_realisation' => $this->date_realisation?->format('Y-m-d'),
            'date_realisation_formatted' => $this->date_realisation?->format('d/m/Y'),
            'lieu' => $this->lieu,
            'categorie' => $this->categorie,
            'categorie_label' => $this->getCategorieLabel(),
            'media_count' => count($media),
            'media' => collect($media)->map(function ($item) {
                return [
                    'type' => $item['type'],
                    'url' => MediaService::getPublicUrl($item['path']),
                    'thumbnail' => $item['type'] === 'image' ? MediaService::getPublicUrl($item['thumbnail'] ?? $item['path']) : null,
                    'original_name' => $item['original_name'] ?? null,
                    'size' => $item['size'] ?? 0,
                    'size_formatted' => MediaService::formatSize($item['size'] ?? 0),
                ];
            })->values()->all(),
            'cover_image' => $this->getCoverImage($media),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    protected function getCoverImage(array $media): ?array
    {
        $first = $media[0] ?? null;
        if (!$first) return null;

        return [
            'type' => $first['type'],
            'url' => MediaService::getPublicUrl($first['path']),
            'thumbnail' => $first['type'] === 'image' ? MediaService::getPublicUrl($first['thumbnail'] ?? $first['path']) : null,
        ];
    }
}

