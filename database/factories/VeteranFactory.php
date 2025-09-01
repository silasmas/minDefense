<?php

namespace Database\Factories;

use App\Models\Veteran;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VeteranFactory extends Factory
{
    protected $model = Veteran::class;

    public function definition(): array
    {
        $f = $this->faker; // fr_FR par défaut si tu veux: Factory::create('fr_FR')

        return [
            'service_number'     => strtoupper('VET-'.date('y').'-'.Str::random(6)),
            'nin'                => $f->boolean(80) ? 'NIN'.str_pad($f->numberBetween(1, 999999), 8, '0', STR_PAD_LEFT) : null,
            'firstname'          => $f->firstName(),
            'lastname'           => $f->lastName(),
            'birthdate'          => $f->dateTimeBetween('-80 years', '-35 years')->format('Y-m-d'),
            'gender'             => $f->randomElement(['male','female']),
            'phone'              => '243'.str_pad($f->numberBetween(100000000, 999999999), 9, '0', STR_PAD_LEFT),
            'email'              => $f->safeEmail(),
            'address'            => $f->streetAddress(),
            'branch'             => $f->randomElement(['Terre','Air','Mer']),
            'rank'               => $f->randomElement(['Soldat','Caporal','Sergent','Adjudant','Lieutenant','Capitaine']),
            'service_start_date' => $f->dateTimeBetween('-40 years', '-20 years')->format('Y-m-d'),
            'service_end_date'   => $f->dateTimeBetween('-20 years', '-5 years')->format('Y-m-d'),
            'status'             => $f->randomElement(['draft','recognized','suspended']),
            'notes'              => $f->boolean(30) ? $f->sentence(12) : null,
        ];
    }
}
