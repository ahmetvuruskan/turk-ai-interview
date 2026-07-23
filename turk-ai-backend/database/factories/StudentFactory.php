<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'surname' => $this->faker->lastName(),
            'number' => $this->faker->numberBetween(15,999999),
            'grade' => $this->faker->numberBetween(1,8),
            'registration_code' => null,
            'parent_id' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
