<?php

namespace Database\Seeders;

use App\Models\StateAsset;
use App\Models\StateAssetAssignment;
use App\Models\StateAssetLog;
use App\Models\Veteran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class StateAssetSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Référentiels géo (centres approx) — RDC
         * (Jitter léger autour du centre pour disperser les points)
         */
        $areas = [
            ['province'=>'Kinshasa',      'city'=>'Kinshasa',    'lat'=>-4.32245,  'lng'=>15.30705,  'jitter'=>0.018],
            ['province'=>'Kongo Central', 'city'=>'Matadi',      'lat'=>-5.81667,  'lng'=>13.45000,  'jitter'=>0.020],
            ['province'=>'Haut-Katanga',  'city'=>'Lubumbashi',  'lat'=>-11.66089, 'lng'=>27.47938,  'jitter'=>0.022],
            ['province'=>'Nord-Kivu',     'city'=>'Goma',        'lat'=>-1.67879,  'lng'=>29.22179,  'jitter'=>0.020],
            ['province'=>'Sud-Kivu',      'city'=>'Bukavu',      'lat'=>-2.51586,  'lng'=>28.86066,  'jitter'=>0.020],
            ['province'=>'Ituri',         'city'=>'Bunia',       'lat'=> 1.56045,  'lng'=>30.25235,  'jitter'=>0.020],
        ];

        /**
         * Catégories affichées côté UI.
         * On mappe aussi vers la clé `material_category` attendue par ton modèle
         * pour la sélection d’icône (vehicle|computer|furniture|medical|default).
         */
        $materielCatsUi = ['véhicule','informatique','mobilier','générateur','équipement'];
        $materielToKey  = [
            'véhicule'     => 'vehicle',
            'informatique' => 'computer',
            'mobilier'     => 'furniture',
            'générateur'   => 'medical',      // on réutilise l’icône "medical" (ou change si tu ajoutes une icône generator)
            'équipement'   => 'default',      // retombera sur default.svg/png
        ];

        $immobilierCatsUi = ['terrain','bâtiment','parcelle','entrepôt'];

        $assetsCount = 120;

        for ($i = 1; $i <= $assetsCount; $i++) {
            $type = rand(0,1) ? 'materiel' : 'immobilier';

            // Catégorie selon type (pour l’UI)
            if ($type === 'materiel') {
                $catUi = $materielCatsUi[array_rand($materielCatsUi)];
                $materialKey = $materielToKey[$catUi] ?? 'default';
            } else {
                $catUi = $immobilierCatsUi[array_rand($immobilierCatsUi)];
                $materialKey = null; // pas utilisé
            }

            // Zone aléatoire + jitter
            $area = $areas[array_rand($areas)];
            $lat  = $area['lat'] + (mt_rand(-1000,1000) / 1000) * $area['jitter'];
            $lng  = $area['lng'] + (mt_rand(-1000,1000) / 1000) * $area['jitter'];

            // Champs communs à tous les assets
            $base = [
                'asset_type'       => $type,
                'asset_code'       => 'ETAT-'.date('Y').'-'.str_pad((string)$i, 6, '0', STR_PAD_LEFT),
                'category'         => $catUi,
                'title'            => ucfirst($catUi).' N° '.Str::upper(Str::random(4)),
                'estimated_value'  => rand(1, 6) * 100000,
                'currency'         => 'CDF',
                'status'           => ['active','under_maintenance','disposed'][array_rand([0,1,2])],
                'acquired_at'      => now()->subYears(rand(0,12))->subDays(rand(0,365)),
                'province'         => $area['province'],
                'city'             => $area['city'],
                'address'          => 'Quartier '.$area['city'],
                'lat'              => $lat,
                'lng'              => $lng,
                'managing_agency'  => 'Min. Défense - Logistique',
            ];

            // Spécifique MATERIEL
            if ($type === 'materiel') {
                $payload = $base + [
                    'serial_number'       => 'SN-'.Str::upper(Str::random(8)),
                    'material_category'   => $materialKey, // <- pour l’icône
                    'material_image_path' => null,         // optionnel (upload)
                ];
            }
            // Spécifique IMMOBILIER (on met un côté pour le carré ; la footprint sera générée par le hook saving())
            else {
                $payload = $base + [
                    'serial_number'   => null,
                    'extent_side_m'   => [40, 50, 60, 80, 100][array_rand([0,1,2,3,4])],
                    // 'footprint'     => null, // laissé à null : généré automatiquement par le modèle si lat/lng/side
                ];
            }

            /** @var \App\Models\StateAsset $asset */
            $asset = StateAsset::create($payload);

            // -------------------------
            // LOGS (1 à 3 lignes)
            // -------------------------
            for ($j = 0; $j < rand(1,3); $j++) {
                StateAssetLog::create([
                    'asset_id'    => $asset->id,                                           // <- garde ton FK d’origine
                    'event_type'  => ['maintenance','inspection','note'][array_rand([0,1,2])],
                    'notes'       => 'Opération '.Str::upper(Str::random(3)),
                    'cost'        => rand(0,1) ? rand(1,10) * 5000 : null,
                    'currency'    => 'CDF',
                    'occurred_at' => Carbon::now()->subDays(rand(0,600)),
                    'lat'         => $asset->lat,
                    'lng'         => $asset->lng,
                ]);
            }

            // -------------------------
            // ASSIGNMENTS (~30% des cas)
            // -------------------------
            if (rand(1,100) <= 30) {
                if (rand(0,1)) {
                    // Affectation à un vétéran existant (si dispo)
                    $vet = Veteran::inRandomOrder()->first();
                    if ($vet) {
                        StateAssetAssignment::create([
                            'asset_id'      => $asset->id,         // <- garde ton FK d’origine
                            'assignee_type' => 'veteran',
                            'veteran_id'    => $vet->id,
                            'service_name'  => null,
                            'assigned_at'   => Carbon::now()->subMonths(rand(0,12)),
                            'status'        => 'ongoing',
                            'notes'         => 'Mise à disposition',
                        ]);
                    }
                } else {
                    // Affectation à un service
                    StateAssetAssignment::create([
                        'asset_id'      => $asset->id,
                        'assignee_type' => 'service',
                        'veteran_id'    => null,
                        'service_name'  => ['Base Aérienne','Entrepôt Central','Atelier Mécanique'][array_rand([0,1,2])],
                        'assigned_at'   => Carbon::now()->subMonths(rand(0,12)),
                        'status'        => 'ongoing',
                        'notes'         => 'Affecté au service',
                    ]);
                }
            }
        }
    }
}
