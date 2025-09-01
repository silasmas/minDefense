<?php

namespace Database\Factories;

use App\Models\Veteran;
use App\Models\VeteranCase;
use App\Models\VeteranPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class VeteranPaymentFactory extends Factory
{
    protected $model = VeteranPayment::class;

    public function definition(): array
    {
        $paidAt = $this->faker->dateTimeBetween('-12 months', 'now');
        return [
            'veteran_id'   => Veteran::factory(),
            'case_id'      => null, // on pourra lier après
            'payment_type' => 'pension',
            'period_month' => $paidAt->format('Y-m-01'),
            'period_start' => $paidAt->format('Y-m-01'),
            'period_end'   => $paidAt->format('Y-m-t'),
            'amount'       => $this->faker->randomElement([120000, 180000, 250000, 300000]), // CDF exemple
            'currency'     => 'CDF',
            'status'       => 'paid',
            'paid_at'      => $paidAt,
            'reference'    => 'REF-'.$this->faker->numberBetween(1000000, 9999999),
            'notes'        => null,
        ];
    }
}
