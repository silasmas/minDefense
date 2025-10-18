<?php
// app/Http/Controllers/Admin/AssetSearchController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VeteranAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as Router;

class AssetSearchController extends Controller
{
    public function index(Request $req)
    {
        try {
            $q = trim((string)$req->query('q',''));

            $query = VeteranAsset::query()
                ->whereNotNull('lat')->whereNotNull('lng');

            if ($q !== '') {
                $query->where(function ($qq) use ($q) {
                    $qq->where('asset_code','like',"%{$q}%")
                       ->orWhere('matricule','like',"%{$q}%")
                       ->orWhere('title','like',"%{$q}%");
                });
            }

            if ($type = $req->query('type'))   $query->where('asset_type',$type);
            if ($status = $req->query('status')) $query->where('status',$status);

            $items = $query->select([
                    'id','asset_code','matricule','title','asset_type','status',
                    'lat','lng','province','city','extent_side_m','footprint','material_image_url',
                ])
                ->latest('id')->limit(2000)->get()
                ->map(function ($a) {
                    $detailRoute = 'filament.admin.resources.veteran-assets.view';
                    $detailUrl   = Router::has($detailRoute)
                        ? route($detailRoute, $a)
                        : url('/'.(filament()->getCurrentPanel()?->getPath() ?? 'admin')."/veteran-assets/{$a->id}");

                    return [
                        'id'    => $a->id,
                        'code'  => $a->asset_code,
                        'matricule' => $a->matricule,
                        'title' => $a->title,
                        'type'  => $a->asset_type, // immobilier|materiel
                        'status'=> $a->status,
                        'lat'   => (float)$a->lat,
                        'lng'   => (float)$a->lng,
                        'city'  => $a->city,
                        'province'=> $a->province,
                        'extent'=> (int)($a->extent_side_m ?? 0),
                        'footprint' => $this->safeFootprint($a->footprint),
                        'icon'  => $a->material_image_url,
                        'url'   => $detailUrl,
                    ];
                })->values();

            return response()->json(['count'=>$items->count(),'items'=>$items], 200);
        } catch (\Throwable $e) {
            // IMPORTANT : JSON même en erreur -> jamais de HTML dans le fetch
            return response()->json([
                'error'=>true,
                'message'=>$e->getMessage(),
            ], 500);
        }
    }

    private function safeFootprint($fp)
    {
        if (!$fp) return null;
        $arr = is_string($fp) ? json_decode($fp, true) : $fp;
        if (!is_array($arr) || count($arr) < 3) return null;
        return collect($arr)->map(function ($p) {
            if (!is_array($p) || count($p) < 2) return null;
            return [(float)$p[0], (float)$p[1]];
        })->filter()->values()->all();
    }
}
