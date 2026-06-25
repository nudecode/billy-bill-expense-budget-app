<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrequencySeeder extends Seeder
{
    public function run(): void
    {
        $frequencies = [
            ['name' => 'Weekly',      'date_add_unit' => 'week',  'date_add_value' => 1],
            ['name' => 'Fortnightly', 'date_add_unit' => 'week',  'date_add_value' => 2],
            ['name' => 'Monthly',     'date_add_unit' => 'month', 'date_add_value' => 1],
            ['name' => 'Quarterly',   'date_add_unit' => 'month', 'date_add_value' => 3],
            ['name' => 'Half-Yearly', 'date_add_unit' => 'month', 'date_add_value' => 6],
            ['name' => 'Annually',    'date_add_unit' => 'year',  'date_add_value' => 1],
        ];

        foreach ($frequencies as $frequency) {
            DB::table('frequencies')->insert(array_merge($frequency, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
