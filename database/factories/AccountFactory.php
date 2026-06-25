<?php
namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name'    => fake()->randomElement(['Direct Debit', 'Savings', 'Credit Card', 'Cheque Account']),
        ];
    }
}
