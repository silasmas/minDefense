<?php

namespace App\Http\Controllers;

use App\Models\VeteranVerification;
use Illuminate\Http\Request;

class VeteranSmsVerifyController extends Controller
{
    // GET /verify/sms/{token}
    public function show(string $token)
    {
        $vrf = VeteranVerification::where('token', $token)->first();
        if (!$vrf || $vrf->consumed_at || $vrf->expires_at->isPast()) {
            return response()->view('public.sms-verify-invalid', [], 410);
        }

        $v = $vrf->veteran()->firstOrFail();

        return view('public.sms-verify', [
            'verify' => $vrf,
            'v'      => $v,
            'map'    => ['draft'=>'Brouillon','recognized'=>'Reconnu','suspended'=>'Suspendu','deceased'=>'Décédé'],
        ]);
    }

    // POST /verify/sms/{token}
    public function confirm(Request $request, string $token)
    {
        $vrf = VeteranVerification::where('token', $token)->first();
        if (!$vrf || $vrf->consumed_at || $vrf->expires_at->isPast()) {
            return response()->view('public.sms-verify-invalid', [], 410);
        }

        $v = $vrf->veteran()->firstOrFail();

        // Option : valider d'éventuels champs payload ici

        // Mettre à jour le statut si demandé
        if ($vrf->next_status) {
            $v->status = $vrf->next_status;
        }
        // Marquer téléphone vérifié
        $v->phone_verified_at = now();
        $v->save();

        $vrf->consumed_at = now();
        $vrf->save();

        return view('public.sms-verify-success', [
            'v'   => $v,
            'map' => ['draft'=>'Brouillon','recognized'=>'Reconnu','suspended'=>'Suspendu','deceased'=>'Décédé'],
        ]);
    }

    public function decline(\Illuminate\Http\Request $request, string $token)
{
    $vrf = \App\Models\VeteranVerification::where('token', $token)->first();
    if (!$vrf || $vrf->consumed_at || $vrf->expires_at->isPast()) {
        return response()->view('public.sms-verify-invalid', [], 410);
    }

    $data = $request->validate([
        'reason' => 'nullable|string|max:500',
    ]);

    // on ne change pas le statut du vétéran ici
    $vrf->declined_at = now();
    $vrf->decline_reason = $data['reason'] ?? null;
    $vrf->consumed_at = now(); // on clôture la demande
    $vrf->save();

    return view('public.sms-verify-declined', [
        'reason' => $vrf->decline_reason,
    ]);
}
}
