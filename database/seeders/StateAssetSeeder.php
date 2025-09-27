<?php

namespace Database\Seeders;

use App\Models\StateAsset;
use App\Models\StateAssetAssignment;
use App\Models\StateAssetLog;
use App\Models\Veteran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StateAssetSeeder extends Seeder
{
    public function run(): void
    {
        // Provinces simplifiées avec centre approx (RDC)
        $areas = [
            ['province'=>'Kinshasa',      'city'=>'Kinshasa',    'lat'=>-4.32245, 'lng'=>15.30705],
            ['province'=>'Kongo Central', 'city'=>'Matadi',      'lat'=>-5.81667, 'lng'=>13.45000],
            ['province'=>'Haut-Katanga',  'city'=>'Lubumbashi',  'lat'=>-11.66089,'lng'=>27.47938],
            ['province'=>'Nord-Kivu',     'city'=>'Goma',        'lat'=>-1.67879, 'lng'=>29.22179],
            ['province'=>'Sud-Kivu',      'city'=>'Bukavu',      'lat'=>-2.51586, 'lng'=>28.86066],
            ['province'=>'Ituri',         'city'=>'Bunia',       'lat'=>1.56045,  'lng'=>30.25235],
        ];

        $materielCats   = ['véhicule','équipement','informatique','outil','générateur'];
        $immobilierCats = ['terrain','bâtiment','parcelle','entrepôt'];

        $assetsCount = 120;

        for ($i=1; $i<=$assetsCount; $i++) {
            $type = rand(0,1) ? 'materiel' : 'immobilier';
            $cat  = $type === 'materiel'
                ? $materielCats[array_rand($materielCats)]
                : $immobilierCats[array_rand($immobilierCats)];

            $area = $areas[array_rand($areas)];

            $asset = StateAsset::create([
                'asset_type' => $type,
                'asset_code' => 'ETAT-'.date('Y').'-'.str_pad((string)$i, 6, '0', STR_PAD_LEFT),
                'category'   => $cat,
                'title'      => ucfirst($cat).' N° '.Str::upper(Str::random(4)),
                'serial_number'   => $type==='materiel' ? 'SN-'.Str::upper(Str::random(8)) : null,
                'estimated_value' => rand(1,6)*100000,
                'currency'   => 'CDF',
                'status'     => ['active','under_maintenance','disposed'][array_rand([0,1,2])],
                'acquired_at'=> now()->subYears(rand(0,12))->subDays(rand(0,365)),
                'province'   => $area['province'],
                'city'       => $area['city'],
                'address'    => 'Quartier '.$area['city'],
                // légères variations autour du centre
                'lat'        => $area['lat'] + (mt_rand(-500,500)/1000),
                'lng'        => $area['lng'] + (mt_rand(-500,500)/1000),
                'managing_agency' => 'Min. Défense - Logistique',
            ]);

            // Journal (1 à 3 lignes)
            for ($j=0; $j<rand(1,3); $j++) {
                StateAssetLog::create([
                    'asset_id'    => $asset->id,
                    'event_type'  => ['maintenance','inspection','note'][array_rand([0,1,2])],
                    'notes'       => 'Opération '.Str::upper(Str::random(3)),
                    'cost'        => rand(0,1) ? rand(1,10)*5000 : null,
                    'currency'    => 'CDF',
                    'occurred_at' => now()->subDays(rand(0,600)),
                    'lat'         => $asset->lat,
                    'lng'         => $asset->lng,
                ]);
            }

            // ~30% d’affectations en cours (à un vétéran OU à un service)
            if (rand(1,100) <= 30) {
                if (rand(0,1)) {
                    // à un vétéran (random)
                    $vet = Veteran::inRandomOrder()->first();
                    if ($vet) {
                        StateAssetAssignment::create([
                            'asset_id'      => $asset->id,
                            'assignee_type' => 'veteran',
                            'veteran_id'    => $vet->id,
                            'assigned_at'   => now()->subMonths(rand(0,12)),
                            'status'        => 'ongoing',
                            'notes'         => 'Mise à disposition',
                        ]);
                    }
                } else {
                    // à un service
                    StateAssetAssignment::create([
                        'asset_id'      => $asset->id,
                        'assignee_type' => 'service',
                        'service_name'  => ['Base Aérienne','Entrepôt Central','Atelier Mécanique'][array_rand([0,1,2])],
                        'assigned_at'   => now()->subMonths(rand(0,12)),
                        'status'        => 'ongoing',
                        'notes'         => 'Affecté au service',
                    ]);
                }
            }
        }
    }
}
