<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Customer::truncate();

        $faker = \Faker\Factory::create();

        // And now, let's create a few customers in our database:
        for ($i = 0; $i < 50; $i++) {
            Customer::create([
                'customer_name' => $faker->name,
                'email' => $faker->safeEmail,
                'api_key' => $faker->bothify('********************************')
            ]);
        }
    }
}
