<?php
namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'name'           => fake()->company(),
            'phone'          => fake()->optional()->phoneNumber(),
            'email'          => fake()->optional()->companyEmail(),
            'account_number' => fake()->optional()->numerify('########'),
        ];
    }
}
