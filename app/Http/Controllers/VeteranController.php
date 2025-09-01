<?php
namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\Veteran;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreVeteranRequest;
use App\Http\Requests\UpdateVeteranRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VeteranController extends Controller
{
    /** mm -> points (DomPDF travaille en points) */
    private function mm2pt(float $mm): float
    {
        return $mm * 72 / 25.4; // 1pt = 1/72"
    }


    private function photoDataUri(?string $disk, ?string $path): ?string
    {
        try {
            $d = $disk ?: 'public';
            if ($path && Storage::disk($d)->exists($path)) {
                $bytes = Storage::disk($d)->get($path);
                $mime  = Storage::disk($d)->mimeType($path) ?? 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($bytes);
            }
        } catch (\Throwable $e) {}
        return null;
    }
    // PDF d'une carte
    public function single(Veteran $veteran)
    {
        // URL signée pour vérification
        $verifyUrl = URL::temporarySignedRoute('veterans.verify', now()->addYear(), ['veteran' => $veteran->id]);

        // ✅ Génère le QR en PNG (pas en SVG) puis base64
        // $qrPng   = QrCode::format('png')->size(220)->margin(0)->generate($verifyUrl);
        // $qrData  = 'data:image/png;base64,'.base64_encode($qrPng);

        // ✅ SVG (aucun besoin d'imagick)
        $qrSvg = QrCode::format('svg')->size(220)->margin(0)->generate($verifyUrl);

        $photoData = $this->toDataUri($veteran->photo_disk, $veteran->photo_path);
        $expires   = $veteran->card_expires_at ?? now()->addYear();

        // Vraie taille CR80 : 85.6 × 54 mm => 242.65 × 153.07 pt
        $w = $this->mm2pt(85.6);
        $h = $this->mm2pt(54);

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'dpi'                  => 300,
            'defaultMediaType'     => 'print',
        ])
            ->loadView('pdf.veteran-card', [
                'v'          => $veteran,
                'photoData'  => $photoData,
                'qrSvg'      => $qrSvg, // <-- SVG ici
                'expires_at' => $expires,
            ])
            ->setPaper([0, 0, $w, $h]); // une carte = une page

        return $pdf->download('carte-' . $veteran->id . '.pdf');
    }


    public function verify(Veteran $veteran): View
    {
        DB::table('card_verifications')->insert([
            'veteran_id'  => $veteran->id,
            'ip'          => request()->ip(),
            'ua'          => request()->userAgent(),
            'verified_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return view('public.veteran-verify', ['veteran' => $veteran]);
    }
     /** Prévisualisation écran (recto/verso) */
    public function preview(Veteran $veteran)
    {
        // -- 1) Paramètres de taille (modifie ici pour agrandir/rétrécir)
        $cardWmm = 150;                                 // largeur carte en millimètres
        $cardHmm = 90;                                  // hauteur carte en millimètres
        $cardWpx = $this->mmToPx($cardWmm);            // conversion mm -> px pour l’écran
        $cardHpx = $this->mmToPx($cardHmm);            // conversion mm -> px pour l’écran

        // -- 2) Images en Data URI (remplace les chemins si besoin)
        $photoData        = $this->toDataUri($veteran->photo_disk ?? null, $veteran->photo_path ?? null);
        $flagDataUri      = $this->assetToDataUri(public_path('img/rdcc.png'));      // slot drapeau
        $logoDataUri      = $this->assetToDataUri(public_path('img/images.jpeg'));  // slot logo
        $signatureDataUri = $this->assetToDataUri(public_path('img/signature.png'));     // signature

        // -- 3) Textes d’entête (personnalisables)
        $title1 = 'REPUBLIQUE DEMOCRATIQUE DU CONGO';
        $title2 = 'Ministère Délégué à La Défense Nationale en Charge des anciens Combattants  ';

        // -- 4) Infos d’émission / signature
        $issued_city   = 'Kinshasa';
        $issued_at     = $veteran->card_issued_at ? Carbon::parse($veteran->card_issued_at) : now();
        $signatoryName = 'Éliézer Ntambwe MPOSHI';

        // -- 5) Lien signé pour le QR (valide 12 mois)
        $verifyUrl = URL::temporarySignedRoute('veterans.verify', now()->addYear(), ['veteran' => $veteran->id]);
        $qrSvg     = QrCode::format('svg')->size(220)->margin(0)->generate($verifyUrl);

        // -- 6) Vue de prévisualisation (HTML)
        return view('cards.preview', [
            'v'                 => $veteran,
            'photoData'         => $photoData,
            'flagDataUri'       => $flagDataUri,
            'logoDataUri'       => $logoDataUri,
            'signatureDataUri'  => $signatureDataUri,
            'title1'            => $title1,
            'title2'            => $title2,
            'issued_city'       => $issued_city,
            'issued_at'         => $issued_at,
            'signatoryName'     => $signatoryName,
            'qrSvg'             => $qrSvg,
            // tailles écran (px) et impression (mm)
            'CARD_W_PX'         => $cardWpx,
            'CARD_H_PX'         => $cardHpx,
            'CARD_W_MM'         => $cardWmm,
            'CARD_H_MM'         => $cardHmm,
        ]);
    }

    /** PDF 2 pages : recto puis verso (taille exacte en mm) */
    public function duplex(Veteran $veteran)
    {
        // -- 1) Même tailles qu’en preview
        $cardWmm = 80;                                  // largeur carte en mm
        $cardHmm = 65;                                   // hauteur carte en mm

        // -- 2) Assets / textes
        $photoData        = $this->toDataUri($veteran->photo_disk ?? null, $veteran->photo_path ?? null);
        $flagDataUri      = $this->assetToDataUri(public_path('images/images.jpeg'));
        $logoDataUri      = $this->assetToDataUri(public_path('images/rdcc.png'));
        $signatureDataUri = $this->assetToDataUri(public_path('images/signature.png'));

        $title1 = 'REPUBLIQUE DEMOCRATIQUE DU CONGO';
        $title2 = 'ANCIENS COMBATTANTS – MINISTÈRE DE LA DÉFENSE';
        $issued_city   = 'Kinshasa';
        $issued_at     = $veteran->card_issued_at ? Carbon::parse($veteran->card_issued_at) : now();
        $signatoryName = 'Jean Pierre TSHIENDA';

        // -- 3) QR
        $verifyUrl = URL::temporarySignedRoute('veterans.verify', now()->addYear(), ['veteran' => $veteran->id]);
        $qrSvg     = QrCode::format('svg')->size(220)->margin(0)->generate($verifyUrl);

        // -- 4) Génération PDF (2 pages)
        $pdf = Pdf::setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'dpi'                  => 300,           // meilleure netteté
            ])
            ->loadView('pdf.veteran-card-duplex', [
                'v'                => $veteran,
                'photoData'        => $photoData,
                'flagDataUri'      => $flagDataUri,
                'logoDataUri'      => $logoDataUri,
                'signatureDataUri' => $signatureDataUri,
                'title1'           => $title1,
                'title2'           => $title2,
                'issued_city'      => $issued_city,
                'issued_at'        => $issued_at,
                'signatoryName'    => $signatoryName,
                'qrSvg'            => $qrSvg,
                'CARD_W_MM'        => $cardWmm,
                'CARD_H_MM'        => $cardHmm,
            ])
            // taille de page exacte = mm -> points
            ->setPaper([0, 0, $this->mmToPt($cardWmm), $this->mmToPt($cardHmm)]);

        return $pdf->download('carte-'.$veteran->id.'-duplex.pdf');
    }

    // -------- Helpers --------

    /** Convertit un fichier disque (storage/public ou public/) en data:URI pour DomPDF */
    protected function assetToDataUri(?string $path): ?string
    {
        if (!$path || !is_file($path)) return null;
        $mime = mime_content_type($path);
        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }

    /** Convertit une image sauvegardée sur un disk Laravel en data:URI */
    protected function toDataUri(?string $disk, ?string $path): ?string
    {
        if (!$disk || !$path) return null;
        $full = storage_path("app/{$disk}/{$path}");
        return $this->assetToDataUri($full);
    }

    /** mm -> points (pour DomPDF setPaper) */
    protected function mmToPt(float $mm): float
    {
        return $mm * 2.83464567;                          // 1 mm = 2.8346 pt
    }

    /** mm -> pixels (pour la preview à ~96 dpi) */
    protected function mmToPx(float $mm): int
    {
        return (int) round($mm * 3.78);                   // 1 mm ≈ 3.78 px
    }
}
