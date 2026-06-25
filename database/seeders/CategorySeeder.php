<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Home/Rent' => [
                'Rates',
                'Body Corporate Fee',
                'Rent',
                'Mortgage',
            ],
            'Utilities' => [
                'Electricity',
                'Gas/Heating',
                'Water',
                'Telephone',
                'Mobile',
                'Internet',
                'Subscriptions',
            ],
            'Food/Groceries' => [
                'Groceries',
                'Restaurant/Fast food',
                'Coffee Pods',
                'Takeaway',
            ],
            'Car/Auto' => [
                'Petrol',
                'Registration',
                'Road Side Assist',
                'Caravan Storage',
                'Servicing/Repairs',
                'Car Wash',
            ],
            'Insurance/Medical' => [
                'Insurance - Medical',
                'Insurance - Auto',
                'Insurance - Home',
                'Insurance - Professional Indemnity',
                'Insurance - Life',
                'Medical Expenses/Co-pay',
            ],
            'Personal care' => [
                'Hair & Beauty',
                'Physio',
                'Gym',
                'Clothing',
            ],
            'Entertainment' => [
                'Movies',
                'Music',
                'DVD rental',
                'Events/Concerts',
            ],
            'Departmental' => [
                'Clothing',
                'Personal items',
                'Kids/Toys',
                'Books/Magazines',
            ],
            'Misc/One-time' => [
                'Vet',
                'Running',
                'Air tickets',
                'Hotel/Lodging',
                'Gifts/Charity',
                'Education',
                'Postage',
            ],
        ];

        foreach ($categories as $categoryName => $subcategories) {
            $categoryId = DB::table('categories')->insertGetId([
                'name'       => $categoryName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($subcategories as $sub) {
                DB::table('subcategories')->insert([
                    'category_id' => $categoryId,
                    'name'        => $sub,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
