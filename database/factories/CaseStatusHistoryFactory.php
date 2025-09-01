<?php

namespace Database\Factories;

use App\Models\VeteranCase;
use App\Models\CaseStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseStatusHistoryFactory extends Factory
{
    protected $model = CaseStatusHistory::class;

    public function definition(): array
    {
        return [
            'case_id'       => VeteranCase::factory(),
            'status'        => $this->faker->randomElement(['draft','submitted','under_review','approved','rejected','closed']),
            'set_by_user_id'=> null,
            'set_at'        => $this->faker->dateTimeBetween('-18 months', 'now'),
            'comment'       => $this->faker->boolean(40) ? $this->faker->sentence(8) : null,
        ];
    }
}
