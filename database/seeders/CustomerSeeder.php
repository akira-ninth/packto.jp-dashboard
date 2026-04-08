<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Cloudflare worker の cloudflare/src/shared/origin.js CUSTOMER_ORIGINS と同期
     */
    public function run(): void
    {
        $pro = Plan::where('slug', 'pro')->firstOrFail();

        Customer::updateOrCreate(
            ['subdomain' => 'rays-hd'],
            [
                'display_name' => 'rays-hd',
                'origin_url' => 'https://rays-hd.com',
                'plan_id' => $pro->id,
                'active' => true,
            ],
        );
    }
}
