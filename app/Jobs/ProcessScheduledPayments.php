<?php

namespace App\Jobs;

use App\Models\VeteranPayment;
use App\Services\SmsSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessScheduledPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SmsSender $sms): void
    {
        VeteranPayment::due()->limit(200)->get()->each(function (VeteranPayment $p) use ($sms) {
            $p->execute();

            if ($p->veteran?->phone) {
                $type = ['pension'=>'pension','arrears'=>'arriérés','aid'=>'aide'][$p->payment_type] ?? $p->payment_type;
                $mois = $p->period_month
                    ? $p->period_month->isoFormat('MMMM YYYY')
                    : (($p->period_start?->format('d/m/Y') ?? '—') . ' - ' . ($p->period_end?->format('d/m/Y') ?? '—'));
                $msg = Str::of('Bonjour {prenom} {nom}, votre paiement {type} de {mois} de {montant} {devise} a été exécuté. Réf: {ref}.')
                    ->replace('{prenom}',  $p->veteran->firstname ?? '')
                    ->replace('{nom}',     $p->veteran->lastname ?? '')
                    ->replace('{type}',    $type)
                    ->replace('{mois}',    $mois)
                    ->replace('{montant}', number_format((float) $p->amount, 0, ' ', ' '))
                    ->replace('{devise}',  $p->currency)
                    ->replace('{ref}',     $p->reference);
                $sms->send($p->veteran->phone, (string) $msg);
            }
        });
    }
}
