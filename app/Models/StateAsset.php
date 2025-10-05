<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StateAsset extends Model
{
    /** @use HasFactory<\Database\Factories\StateAssetFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'acquired_at'   => 'date',
        'disposed_at'   => 'date',
        'lat'           => 'float',
        'lng'           => 'float',
        'photos'        => 'array',
        'extent_side_m' => 'integer',
        'footprint'     => 'array', // tableau de [lat, lng] fermant la boucle
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(StateAssetLog::class, 'asset_id')->latest('occurred_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StateAssetAssignment::class, 'asset_id')->latest('assigned_at');
    }
    public function isImmobilier(): bool
    {
        return $this->asset_type === 'immobilier';
    }

    /**
     * Construit un carré (approx.) autour d'un centre (lat,lng) de côté $sideM (m).
     * Retourne un polygone fermé: [[lat,lng],..., [lat,lng]]
     */
    public static function makeSquareFootprint(float $lat, float $lng, int $sideM): array
    {
        // Conversion m -> degrés (approx) : 1° lat ~ 111_320 m ; 1° lng ~ 111_320 * cos(lat)
        $degLat = $sideM / 111_320;
        $degLng = $sideM / (111_320 * max(cos(deg2rad($lat)), 1e-6));

        // On veut un carré centré : demi-côté en degrés
        $dLat = $degLat / 2;
        $dLng = $degLng / 2;

                                            // Points (ordre horaire) + fermeture (retour au 1er point)
        $p1 = [$lat + $dLat, $lng - $dLng]; // NW
        $p2 = [$lat + $dLat, $lng + $dLng]; // NE
        $p3 = [$lat - $dLat, $lng + $dLng]; // SE
        $p4 = [$lat - $dLat, $lng - $dLng]; // SW

        return [$p1, $p2, $p3, $p4, $p1];
    }

    /**
     * Avant sauvegarde: si IMMOBILIER et qu'on a lat/lng + side, (re)génère la footprint carrée.
     */
    protected static function booted(): void
    {
        static::saving(function (StateAsset $asset) {
            if ($asset->isImmobilier() && $asset->lat && $asset->lng && $asset->extent_side_m) {
                $asset->footprint = self::makeSquareFootprint($asset->lat, $asset->lng, $asset->extent_side_m);
            } else {
                // Pour un bien matériel, on ne force pas de polygone
                if ($asset->asset_type === 'materiel') {
                    $asset->footprint = null;
                }
            }
        });
    }

    /**
     * Image "par défaut" selon la catégorie matériel (si pas d'upload).
     * Place tes fichiers dans public/images/materials/.
     */
    // public function getMaterialImageUrlAttribute(): ?string
    // {
    //     if ($this->material_image_path) {
    //         return asset('storage/' . $this->material_image_path);
    //     }

    //     $map = [
    //         'vehicle'   => asset('images/materials/vehicle.svg'),
    //         'computer'  => asset('images/materials/computer.svg'),
    //         'furniture' => asset('images/materials/furniture.svg'),
    //         'medical'   => asset('images/materials/medical.svg'),
    //     ];

    //     return $map[$this->material_category] ?? asset('images/materials/default.svg');
    // }
    public function getMaterialImageUrlAttribute(): ?string
    {
        // 1) Si une image a été uploadée via FileUpload (disk=public / directory=materials)
        if ($this->material_image_path) {
            // ex: materials/generateur.jpg
            return Storage::disk('public')->url($this->material_image_path);
        }

        // 2) Sinon, on sert une icône par défaut dans /public/images/materials/
        //    On essaie d’abord .svg puis .png pour être compatible avec ton dossier actuel.
        $key = $this->material_category ?: 'default'; // vehicle|computer|furniture|medical|default
        $candidates = [
            public_path("images/materials/{$key}.svg"),
            public_path("images/materials/{$key}.png"),
            public_path("images/materials/default.svg"),
            public_path("images/materials/default.png"),
        ];

        foreach ($candidates as $file) {
            if (is_file($file)) {
                // transforme en URL publique
                $rel = str_replace(public_path(), '', $file);
                return asset(ltrim($rel, '/\\'));
            }
        }

        // 3) Ultime fallback: une data-URI vide (évite « image cassée »)
        return 'data:image/gif;base64,R0lGODlhAQABAAAAACw=';
    }
}
