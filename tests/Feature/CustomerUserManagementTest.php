<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 13i: 顧客ページからの user 追加 / 削除。
 */
class CustomerUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;
    private User $master;
    private User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $pro = Plan::create([
            'slug' => 'pro',
            'name' => 'Pro',
            'features' => ['image' => true, 'text' => true],
        ]);

        $this->customer = Customer::create([
            'subdomain' => 'rays-hd',
            'display_name' => 'rays-hd',
            'origin_url' => 'https://rays-hd.com',
            'plan_id' => $pro->id,
            'active' => true,
        ]);

        $this->master = User::create([
            'name' => 'Packto Master',
            'email' => 'master@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_MASTER,
            'customer_id' => null,
        ]);

        $this->tenant = User::create([
            'name' => 'rays-hd admin',
            'email' => 'rays-hd@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_master_can_add_user_to_customer(): void
    {
        $response = $this->actingAs($this->master)
            ->post('http://'.config('app.admin_domain')."/customers/{$this->customer->id}/users", [
                'name' => 'Second User',
                'email' => 'second@rays-hd.example',
            ]);

        $response->assertRedirect(route('admin.customers.show', $this->customer));
        $response->assertSessionHas('temp_credentials.email', 'second@rays-hd.example');

        $created = User::where('email', 'second@rays-hd.example')->firstOrFail();
        $this->assertEquals(User::ROLE_CUSTOMER, $created->role);
        $this->assertEquals($this->customer->id, $created->customer_id);

        $tempPassword = session('temp_credentials.password');
        $this->assertNotNull($tempPassword);
        $this->assertTrue(Hash::check($tempPassword, $created->password));
    }

    public function test_master_can_delete_customer_user(): void
    {
        $response = $this->actingAs($this->master)
            ->delete('http://'.config('app.admin_domain')."/customers/{$this->customer->id}/users/{$this->tenant->id}");

        $response->assertRedirect(route('admin.customers.show', $this->customer));
        $this->assertDatabaseMissing('users', ['id' => $this->tenant->id]);
    }

    public function test_cannot_delete_user_belonging_to_other_customer(): void
    {
        $basic = Plan::create([
            'slug' => 'basic',
            'name' => 'Basic',
            'features' => ['image' => true, 'text' => false],
        ]);
        $other = Customer::create([
            'subdomain' => 'other',
            'display_name' => 'other',
            'origin_url' => 'https://other.example',
            'plan_id' => $basic->id,
            'active' => true,
        ]);

        $response = $this->actingAs($this->master)
            ->delete('http://'.config('app.admin_domain')."/customers/{$other->id}/users/{$this->tenant->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $this->tenant->id]);
    }

    public function test_cannot_delete_master_user_via_customer_user_route(): void
    {
        // master を customer に紐付けて (一時的に) destroy を試す
        $this->master->customer_id = $this->customer->id;
        $this->master->save();

        $response = $this->actingAs($this->master)
            ->delete('http://'.config('app.admin_domain')."/customers/{$this->customer->id}/users/{$this->master->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $this->master->id]);
    }

    public function test_customer_user_routes_blocked_for_customer_role(): void
    {
        $response = $this->actingAs($this->tenant)
            ->post('http://'.config('app.admin_domain')."/customers/{$this->customer->id}/users", [
                'name' => 'Hack',
                'email' => 'hack@example.com',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'hack@example.com']);
    }
}
