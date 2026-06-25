<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class FrequencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'           => 'Monthly',
            'date_add_unit'  => 'month',
            'date_add_value' => 1,
        ];
    }
}
