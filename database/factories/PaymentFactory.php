<?php
namespace Database\Factories;
use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'biller_id'    => Biller::factory(),
            'category_id'  => Category::factory(),
            'account_id'   => Account::factory(),
            'amount'       => fake()->randomFloat(2, 5, 500),
            'payment_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
