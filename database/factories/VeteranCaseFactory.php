<?php

namespace Database\Factories;

use App\Models\Veteran;
use App\Models\VeteranCase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VeteranCaseFactory extends Factory
{
    protected $model = VeteranCase::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['status','pension','healthcard','aid']);
        $num  = 'DSR-'.date('Y').'-'.str_pad($this->faker->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT);

        return [
            'veteran_id'     => Veteran::factory(),
            'case_number'    => $num,
            'case_type'      => $type,
            'current_status' => $this->faker->randomElement(['draft','submitted','under_review','approved','rejected','closed']),
            'opened_at'      => $this->faker->dateTimeBetween('-18 months', '-2 months'),
            'closed_at'      => $this->faker->boolean(40) ? $this->faker->dateTimeBetween('-2 months', 'now') : null,
            'summary'        => $this->faker->boolean(60) ? $this->faker->sentence(10) : null,
            'meta'           => null,
        ];
    }
}
