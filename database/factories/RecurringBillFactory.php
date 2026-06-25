<?php
namespace Database\Factories;
use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\Frequency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringBillFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 year', 'now');
        return [
            'user_id'      => User::factory(),
            'biller_id'    => Biller::factory(),
            'frequency_id' => Frequency::factory(),
            'category_id'  => Category::factory(),
            'account_id'   => Account::factory(),
            'amount'       => fake()->randomFloat(2, 5, 500),
            'start_date'   => $start,
            'end_date'     => Carbon::instance($start)->addYear()->toDateString(),
        ];
    }
}
