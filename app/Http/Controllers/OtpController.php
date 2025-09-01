<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\User;
use App\Models\Veteran;
use App\Services\SmsSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{
    // 1) Envoi OTP pour reset mot de passe (via téléphone)
    public function sendReset(Request $req, SmsSender $sms)
    {
        $data = $req->validate([
            'phone' => 'required|string|max:30',
        ]);

        // Trouver l'utilisateur par téléphone
        $user = User::where('phone', $data['phone'])->first();
        if (!$user) {
            // Option: essayer côté Veteran (si tu veux gérer un portail VRN séparé)
            $vet = Veteran::where('phone', $data['phone'])->first();
            if ($vet) {
                $ownerType = 'veteran';
                $ownerId   = $vet->id;
            } else {
                return response()->json(['message' => 'Téléphone introuvable'], 404);
            }
        } else {
            $ownerType = 'user';
            $ownerId   = $user->id;
        }

        $code = (string) random_int(100000, 999999);
        OtpCode::create([
            'owner_type' => $ownerType,
            'owner_id'   => $ownerId,
            'phone'      => $data['phone'],
            'purpose'    => 'password_reset',
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
            'ip'         => $req->ip(),
        ]);

        $ok = $sms->send($data['phone'], "Code de vérification: {$code} (valable 10 min)");
        return response()->json(['status' => $ok ? 'sent' : 'failed']);
    }

    // 2) Vérification OTP + mise à jour du mot de passe
    public function verifyReset(Request $req)
    {
        $data = $req->validate([
            'phone'    => 'required|string|max:30',
            'code'     => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $otp = OtpCode::active()
            ->where('phone', $data['phone'])
            ->where('purpose', 'password_reset')
            ->where('code', $data['code'])
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Code invalide ou expiré'], 422);
        }

        $otp->attempts++;
        if ($otp->attempts > 5) {
            $otp->consumed_at = now();
            $otp->save();
            return response()->json(['message' => 'Trop de tentatives'], 429);
        }

        // Si owner = user -> change password
        if ($otp->owner_type === 'user') {
            $user = User::find($otp->owner_id);
            if (!$user) return response()->json(['message' => 'Utilisateur introuvable'], 404);
            $user->password = Hash::make($data['password']);
            $user->save();
        } else {
            // Cas "veteran" : à relier à ton système d’auth si tu as un compte séparé
            // Ex: créer/mapper un user pour ce veteran ici si besoin.
        }

        $otp->consumed_at = now();
        $otp->save();

        return response()->json(['status' => 'password_updated']);
    }

    // 3) Envoi OTP pour LOGIN (2FA ou mot de passe-less)
    public function sendLogin(Request $req, SmsSender $sms)
    {
        $data = $req->validate([
            'phone' => 'required|string|max:30',
        ]);

        $user = User::where('phone', $data['phone'])->first();
        if (!$user) return response()->json(['message' => 'Téléphone introuvable'], 404);

        $code = (string) random_int(100000, 999999);
        OtpCode::create([
            'owner_type' => 'user',
            'owner_id'   => $user->id,
            'phone'      => $data['phone'],
            'purpose'    => 'login',
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
            'ip'         => $req->ip(),
        ]);

        $ok = $sms->send($data['phone'], "Code de connexion: {$code} (10 min)");
        return response()->json(['status' => $ok ? 'sent' : 'failed']);
    }

    // 4) Vérif OTP LOGIN et connexion
    public function verifyLogin(Request $req)
    {
        $data = $req->validate([
            'phone' => 'required|string|max:30',
            'code'  => 'required|string|size:6',
        ]);

        $otp = OtpCode::active()
            ->where('phone', $data['phone'])
            ->where('purpose', 'login')
            ->where('code', $data['code'])
            ->first();

        if (!$otp) return response()->json(['message' => 'Code invalide ou expiré'], 422);

        $user = \App\Models\User::find($otp->owner_id);
        if (!$user) return response()->json(['message' => 'Utilisateur introuvable'], 404);

        $otp->consumed_at = now();
        $otp->save();

        Auth::login($user); // session web
        return response()->json(['status' => 'logged_in']);
    }
}
