<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Cloudflare worker 側 cloudflare/src/shared/plans.js の PLAN_FEATURES と同期
     */
    public function run(): void
    {
        Plan::updateOrCreate(
            ['slug' => 'basic'],
            [
                'name' => 'Basic',
                'features' => ['image' => true, 'text' => false],
            ],
        );

        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'features' => ['image' => true, 'text' => true],
            ],
        );
    }
}
