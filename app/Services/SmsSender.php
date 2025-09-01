<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SmsSender
{
    // public function send(string $to, string $message): bool
    // {
    //     $driver = config('services.sms.driver', 'log');

    //     if ($driver === 'twilio') {
    //         $sid   = config('services.twilio.sid');
    //         $token = config('services.twilio.token');
    //         $from  = config('services.twilio.from');
    //         try {
    //             $twilio = new Client($sid, $token);
    //             $twilio->messages->create($to, ['from' => $from, 'body' => $message]);
    //             return true;
    //         } catch (\Throwable $e) {
    //             Log::error('Twilio SMS error: '.$e->getMessage());
    //             return false;
    //         }
    //     }

    //     // Fallback: log
    //     Log::info("SMS to {$to}: {$message}");
    //     return true;
    // }
     function send($phoneNumber, $message)
    {
        // URL de l'API de Keccel (remplacez par l'URL réelle)
        $apiUrl = env('SMS_URL');

        // Clé API ou identifiants d'authentification (remplacez par vos informations)
        $apiKey = env('SMS_TOKEN');

        // Données à envoyer
        $postData = [
            "token" => $apiKey,    // taken
            "to" => $phoneNumber,    // Numéro de téléphone du destinataire
            "from" => env('SMS_FROM'), // Optionnel : Nom ou numéro de l'expéditeur
            "message" => $message,   // Contenu du message
        ];

        // Initialisation de cURL
        $ch = curl_init();

        // Configuration de la requête
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); // Conversion des données en JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey", // Clé API incluse dans les en-têtes
        ]);

        // Exécuter la requête
        $response = curl_exec($ch);

        // Vérifier les erreurs
        if (curl_errno($ch)) {
            echo "Erreur cURL : " . curl_error($ch);
        }

        // Décoder la réponse
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Afficher la réponse pour débogage
        return [
            "status_code" => $responseCode,
            "response" => json_decode($response, true),
        ];
    }
}
