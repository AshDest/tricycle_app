<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Obtenir une valeur de paramètre
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Définir une valeur de paramètre
     */
    public static function set(string $key, $value, ?string $type = null, ?string $group = null): void
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type ?? 'string',
                'group' => $group ?? 'general',
            ]
        );

        Cache::forget("setting_{$key}");
    }

    /**
     * Convertir la valeur selon son type
     */
    protected static function castValue($value, $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'decimal', 'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Obtenir tous les paramètres d'un groupe
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => static::castValue($setting->value, $setting->type)];
            })
            ->toArray();
    }

    /**
     * Obtenir le montant journalier par défaut
     */
    public static function getMontantJournalierDefaut(): float
    {
        return static::get('montant_journalier_defaut', 5000);
    }

    /**
     * Vider le cache des paramètres
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }
    }
}
