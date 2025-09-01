<?php

namespace Database\Seeders;

use App\Models\Veteran;
use App\Models\VeteranCase;
use App\Models\CaseStatusHistory;
use App\Models\VeteranPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class VeteranSeeder extends Seeder
{
    public function run(): void
    {
        // 120 vétérans
        Veteran::factory(120)->create()->each(function (Veteran $vet) {
            // 1 dossier "status" systématique
            $caseStatus = VeteranCase::factory()->create([
                'veteran_id' => $vet->id,
                'case_type'  => 'status',
                'current_status' => 'approved',
            ]);

            // Historique simple
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

            // ~60% ont un dossier "pension" avec paiements
            if (random_int(1, 100) <= 60) {
                $casePension = VeteranCase::factory()->create([
                    'veteran_id' => $vet->id,
                    'case_type'  => 'pension',
                    'current_status' => 'approved',
                ]);

                // Historique du cas pension
                $opened2 = Carbon::parse($casePension->opened_at ?? now()->subMonths(6));
                foreach (['draft','submitted','under_review','approved'] as $i => $st) {
                    CaseStatusHistory::create([
                        'case_id' => $casePension->id,
                        'status'  => $st,
                        'set_at'  => $opened2->copy()->addDays($i * 7),
                    ]);
                }

                // Paiements des 6 derniers mois
                $months = random_int(4, 8);
                for ($m = $months; $m >= 1; $m--) {
                    $period = Carbon::now()->startOfMonth()->subMonths($m-1);
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
                        'reference'    => 'REF-'.$vet->id.'-'.$period->format('Ym'),
                        'notes'        => null,
                    ]);
                }
            }
        });
    }
}
