<?php

namespace Database\Seeders;

use App\Helpers\API\Shopify;
use App\Helpers\API\Woocommerce;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $now = date("Y-m-d H:i:s");

        //
        DB::table('stores')->insert([
            'id' => 1,
            'name' => "Shopify",
            'api_class' => Shopify::class,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('stores')->insert([
            'id' => 2,
            'name' => "WooCommerce",
            'api_class' => Woocommerce::class,
            'created_at' => $now,
            'updated_at' => $now
        ]);

    }
}
