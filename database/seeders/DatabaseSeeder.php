<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            CustomerSeeder::class,
        ]);

        // master アカウント (admin.packto.jp ログイン用)
        User::updateOrCreate(
            ['email' => 'master@packto.jp'],
            [
                'name' => 'Packto Master',
                'password' => Hash::make('changeme'),
                'role' => User::ROLE_MASTER,
                'customer_id' => null,
            ],
        );

        // rays-hd 顧客アカウント (app.packto.jp ログイン用)
        $rays = Customer::where('subdomain', 'rays-hd')->firstOrFail();
        User::updateOrCreate(
            ['email' => 'rays-hd@packto.jp'],
            [
                'name' => 'rays-hd admin',
                'password' => Hash::make('changeme'),
                'role' => User::ROLE_CUSTOMER,
                'customer_id' => $rays->id,
            ],
        );
    }
}
