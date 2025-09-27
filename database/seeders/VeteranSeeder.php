<?php

namespace Database\Seeders;

use App\Models\Veteran;
use App\Models\VeteranCase;
use App\Models\CaseStatusHistory;
use App\Models\VeteranPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class VeteranSeeder extends Seeder
{
    public function run(): void
    {
        $nowMonth   = Carbon::now()->startOfMonth();
        $nextMonth  = $nowMonth->copy()->addMonth();

        $eligibleScheduledCount = 0; // compteur de "programmés" (mois courant)

        // 120 vétérans
        Veteran::factory(120)->create()->each(function (Veteran $vet) use (&$eligibleScheduledCount, $nowMonth, $nextMonth) {

            // 1 dossier "status" systématique
            $caseStatus = VeteranCase::factory()->create([
                'veteran_id'     => $vet->id,
                'case_type'      => 'status',
                'current_status' => 'approved',
            ]);

            // Historique du dossier "status"
            $opened = Carbon::parse($caseStatus->opened_at ?? now()->subMonths(6));
            $events = [
                ['status' => 'draft',        't' => $opened->copy()],
                ['status' => 'submitted',    't' => $opened->copy()->addDays(3)],
                ['status' => 'under_review', 't' => $opened->copy()->addDays(10)],
                ['status' => 'approved',     't' => $opened->copy()->addDays(30)],
            ];
            foreach ($events as $e) {
                CaseStatusHistory::create([
                    'case_id' => $caseStatus->id,
                    'status'  => $e['status'],
                    'set_at'  => $e['t'],
                    'comment' => null,
                ]);
            }

            // ~60% ont un dossier "pension" avec paiements (déjà payés) sur les 4–8 derniers mois
            $hasPensionCase = false;
            if (random_int(1, 100) <= 60) {
                $casePension = VeteranCase::factory()->create([
                    'veteran_id'     => $vet->id,
                    'case_type'      => 'pension',
                    'current_status' => 'approved',
                ]);
                $hasPensionCase = true;

                // Historique du cas pension
                $opened2 = Carbon::parse($casePension->opened_at ?? now()->subMonths(6));
                foreach (['draft','submitted','under_review','approved'] as $i => $st) {
                    CaseStatusHistory::create([
                        'case_id' => $casePension->id,
                        'status'  => $st,
                        'set_at'  => $opened2->copy()->addDays($i * 7),
                    ]);
                }

                // Paiements des 4–8 derniers mois (PAID)
                $months = random_int(4, 8);
                for ($m = $months; $m >= 1; $m--) {
                    $period = Carbon::now()->startOfMonth()->subMonths($m - 1);
                    VeteranPayment::create([
                        'veteran_id'   => $vet->id,
                        'case_id'      => $casePension->id,
                        'payment_type' => 'pension',
                        'period_month' => $period->copy()->startOfMonth(),
                        'period_start' => $period->copy()->startOfMonth(),
                        'period_end'   => $period->copy()->endOfMonth(),
                        'amount'       => collect([120000, 180000, 250000, 300000])->random(),
                        'currency'     => 'CDF',
                        'status'       => 'paid',
                        'paid_at'      => $period->copy()->endOfMonth()->setTime(12, 0),
                        'reference'    => 'REF-' . $vet->id . '-' . $period->format('Ym'),
                        'notes'        => null,
                    ]);
                }
            }

            // ————————————————————————————————————————————————
            // AJOUT : Générer des paiements "programmés" (éligibles)
            // ————————————————————————————————————————————————

            // 1) ÉLIGIBLES À PAYER — Mois courant (pension, scheduled)
            // On n’insère QUE si le vétéran n’a PAS déjà une pension "paid" pour le mois courant.
            $hasPaidThisMonth = VeteranPayment::where('veteran_id', $vet->id)
                ->where('payment_type', 'pension')
                ->whereDate('period_month', $nowMonth)
                ->where('status', 'paid')
                ->exists();

            // On vise environ 35 vétérans "éligibles"
            if (! $hasPaidThisMonth && $eligibleScheduledCount < 35) {
                // s’assurer qu’il a un téléphone pour l’export SMS en vrac
                if (empty($vet->phone)) {
                    // num pseudo aléatoire +2438XXXXXXXX
                    $rand = str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT);
                    $vet->phone = '+2438' . $rand;
                    $vet->save();
                }

                // moitié "due" (paid_at <= now), moitié "sans date"
                $makeDue = (bool) random_int(0, 1);

                // Référence unique
                do {
                    $ref = sprintf('REF-%d-%s-%s',
                        $vet->id,
                        now()->format('ym'),
                        Str::upper(Str::random(5))
                    );
                } while (VeteranPayment::where('reference', $ref)->exists());

                VeteranPayment::create([
                    'veteran_id'   => $vet->id,
                    'case_id'      => $hasPensionCase ? ($casePension->id ?? null) : null,
                    'payment_type' => 'pension',
                    'period_month' => $nowMonth,                      // 🟢 MOIS COURANT
                    'period_start' => $nowMonth->copy()->startOfMonth(),
                    'period_end'   => $nowMonth->copy()->endOfMonth(),
                    'amount'       => collect([120000, 180000, 250000, 300000])->random(),
                    'currency'     => 'CDF',
                    'status'       => 'scheduled',                    // 🟢 PROGRAMMÉ
                    'paid_at'      => $makeDue ? now()->subHours(random_int(1, 72)) : null, // due vs sans date
                    'reference'    => $ref,
                    'notes'        => 'Paiement programmé (seed).',
                ]);

                $eligibleScheduledCount++;
            }

            // 2) À VENIR — Mois prochain (pension, scheduled, paid_at au milieu du mois)
            if (random_int(1, 100) <= 30) {
                // Référence unique
                do {
                    $ref2 = sprintf('REF-%d-%s-%s',
                        $vet->id,
                        $nextMonth->format('ym'),
                        Str::upper(Str::random(5))
                    );
                } while (VeteranPayment::where('reference', $ref2)->exists());

                VeteranPayment::updateOrCreate(
                    [
                        'veteran_id'   => $vet->id,
                        'payment_type' => 'pension',
                        'period_month' => $nextMonth, // contrainte unique respectée
                    ],
                    [
                        'case_id'      => $hasPensionCase ? ($casePension->id ?? null) : null,
                        'period_start' => $nextMonth->copy()->startOfMonth(),
                        'period_end'   => $nextMonth->copy()->endOfMonth(),
                        'amount'       => collect([120000, 180000, 250000, 300000])->random(),
                        'currency'     => 'CDF',
                        'status'       => 'scheduled', // “à venir”
                        'paid_at'      => $nextMonth->copy()->addDays(15)->setTime(10, 0), // milieu de mois prochain
                        'reference'    => $ref2,
                        'notes'        => 'Paiement à venir (seed).',
                    ]
                );
            }
        });

        $this->command?->info('Seed terminé : vétérans + dossiers + historiques + paiements (paid & scheduled).');
    }
}
