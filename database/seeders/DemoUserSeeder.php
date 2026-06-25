<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'              => 'Glen Allen',
            'email'             => 'glen@example.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Accounts
        $directDebitId = DB::table('accounts')->insertGetId([
            'user_id'    => $userId,
            'name'       => 'Direct Debit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Billers from HomeBudget report
        $billers = [
            'MBRC',
            'Marin BC',
            'Altogether Group',
            'Unity Water',
            'AAPI',
            'Zanda',
            'Apple Google Photos',
            'Apple Cloud+ Storage',
            'Adobe',
            'Netflix',
            'Telstra Kayo',
            'Coles',
            'Nespresso',
            'Coffee/Breakfast',
            'Petrol',
            'Bridgestone Tyres',
            'Main Roads',
            'Watson Park Caravan Storage',
            'Medibank',
            'AHM',
            'RACQ Auto',
            'Scarborough Physio',
            'JB Beauty',
            'Kidmans Hair',
            'Noo Bowker Run Coach',
        ];

        $billerIds = [];
        foreach ($billers as $name) {
            $billerIds[$name] = DB::table('billers')->insertGetId([
                'user_id'    => $userId,
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Recurring income — VA PPH fortnightly $4,333
        $fortnightlyId = DB::table('frequencies')->where('name', 'Fortnightly')->value('id');
        DB::table('recurring_income')->insert([
            'user_id'    => $userId,
            'name'       => 'VA PPH',
            'frequency_id' => $fortnightlyId,
            'account_id' => $directDebitId,
            'amount'     => 4333.00,
            'start_date' => '2026-01-02',
            'end_date'   => '2027-12-31',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Recurring bills — a representative set from the report
        $monthlyId = DB::table('frequencies')->where('name', 'Monthly')->value('id');
        $weeklyId  = DB::table('frequencies')->where('name', 'Weekly')->value('id');

        $utilitiesCatId    = DB::table('categories')->where('name', 'Utilities')->value('id');
        $subscriptionsSubId = DB::table('subcategories')->where('name', 'Subscriptions')->value('id');
        $electricitySubId   = DB::table('subcategories')->where('name', 'Electricity')->value('id');
        $waterSubId         = DB::table('subcategories')->where('name', 'Water')->value('id');
        $homeRentCatId      = DB::table('categories')->where('name', 'Home/Rent')->value('id');
        $ratesSubId         = DB::table('subcategories')->where('name', 'Rates')->value('id');
        $bodyCorpSubId      = DB::table('subcategories')->where('name', 'Body Corporate Fee')->value('id');
        $foodCatId          = DB::table('categories')->where('name', 'Food/Groceries')->value('id');
        $groceriesSubId     = DB::table('subcategories')->where('name', 'Groceries')->value('id');
        $carCatId           = DB::table('categories')->where('name', 'Car/Auto')->value('id');
        $petrolSubId        = DB::table('subcategories')->where('name', 'Petrol')->value('id');
        $insuranceCatId     = DB::table('categories')->where('name', 'Insurance/Medical')->value('id');
        $medInsSubId        = DB::table('subcategories')->where('name', 'Insurance - Medical')->value('id');
        $autoInsSubId       = DB::table('subcategories')->where('name', 'Insurance - Auto')->value('id');

        $recurringBills = [
            ['biller' => 'Netflix',           'amount' => 28.99,  'frequency' => $monthlyId,  'category' => $utilitiesCatId,   'subcategory' => $subscriptionsSubId, 'start' => '2026-01-21'],
            ['biller' => 'Adobe',             'amount' => 23.99,  'frequency' => $monthlyId,  'category' => $utilitiesCatId,   'subcategory' => $subscriptionsSubId, 'start' => '2026-01-15'],
            ['biller' => 'Zanda',             'amount' => 57.53,  'frequency' => $monthlyId,  'category' => $utilitiesCatId,   'subcategory' => $subscriptionsSubId, 'start' => '2026-01-02'],
            ['biller' => 'Telstra Kayo',      'amount' => 45.99,  'frequency' => $monthlyId,  'category' => $utilitiesCatId,   'subcategory' => $subscriptionsSubId, 'start' => '2026-01-23'],
            ['biller' => 'AAPI',              'amount' => 150.00, 'frequency' => $monthlyId,  'category' => $utilitiesCatId,   'subcategory' => $subscriptionsSubId, 'start' => '2026-01-01'],
            ['biller' => 'Apple Google Photos','amount' => 4.49,  'frequency' => $monthlyId,  'category' => $utilitiesCatId,   'subcategory' => $subscriptionsSubId, 'start' => '2026-01-14'],
            ['biller' => 'Apple Cloud+ Storage','amount' => 4.49, 'frequency' => $monthlyId,  'category' => $utilitiesCatId,   'subcategory' => $subscriptionsSubId, 'start' => '2026-01-15'],
            ['biller' => 'Altogether Group',  'amount' => 95.00,  'frequency' => $fortnightlyId, 'category' => $utilitiesCatId,'subcategory' => $electricitySubId,   'start' => '2026-01-05'],
            ['biller' => 'Unity Water',       'amount' => 70.00,  'frequency' => $fortnightlyId, 'category' => $utilitiesCatId,'subcategory' => $waterSubId,         'start' => '2026-01-05'],
            ['biller' => 'MBRC',              'amount' => 140.00, 'frequency' => $fortnightlyId, 'category' => $homeRentCatId, 'subcategory' => $ratesSubId,         'start' => '2026-01-05'],
            ['biller' => 'Marin BC',          'amount' => 260.00, 'frequency' => $fortnightlyId, 'category' => $homeRentCatId, 'subcategory' => $bodyCorpSubId,      'start' => '2026-01-05'],
            ['biller' => 'Coles',             'amount' => 300.00, 'frequency' => $weeklyId,   'category' => $foodCatId,        'subcategory' => $groceriesSubId,     'start' => '2026-01-08'],
            ['biller' => 'Medibank',          'amount' => 317.61, 'frequency' => $fortnightlyId, 'category' => $insuranceCatId,'subcategory' => $medInsSubId,        'start' => '2026-01-05'],
            ['biller' => 'AHM',              'amount' =>  92.95, 'frequency' => $fortnightlyId, 'category' => $insuranceCatId,'subcategory' => $medInsSubId,         'start' => '2026-01-09'],
            ['biller' => 'RACQ Auto',         'amount' => 221.00, 'frequency' => $monthlyId,  'category' => $insuranceCatId,   'subcategory' => $autoInsSubId,       'start' => '2026-01-10'],
            ['biller' => 'Watson Park Caravan Storage', 'amount' => 86.00, 'frequency' => $monthlyId, 'category' => $carCatId, 'subcategory' => $petrolSubId,        'start' => '2026-01-19'],
        ];

        foreach ($recurringBills as $bill) {
            DB::table('recurring_bills')->insert([
                'user_id'        => $userId,
                'biller_id'      => $billerIds[$bill['biller']],
                'frequency_id'   => $bill['frequency'],
                'category_id'    => $bill['category'],
                'subcategory_id' => $bill['subcategory'],
                'account_id'     => $directDebitId,
                'amount'         => $bill['amount'],
                'start_date'     => $bill['start'],
                'end_date'       => '2027-12-31',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}
