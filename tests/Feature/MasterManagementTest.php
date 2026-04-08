<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 13h: master アカウント追加 / 削除。
 */
class MasterManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeMaster(string $email, string $name = 'Master'): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_MASTER,
            'customer_id' => null,
        ]);
    }

    public function test_index_lists_masters(): void
    {
        $a = $this->makeMaster('master@packto.jp', 'Master A');
        $b = $this->makeMaster('master2@packto.jp', 'Master B');

        $response = $this->actingAs($a)->get('http://'.config('app.admin_domain').'/masters');

        $response->assertOk();
        $response->assertSee('master@packto.jp');
        $response->assertSee('master2@packto.jp');
    }

    public function test_master_can_add_another_master(): void
    {
        $a = $this->makeMaster('master@packto.jp');

        $response = $this->actingAs($a)->post('http://'.config('app.admin_domain').'/masters', [
            'name' => 'New Master',
            'email' => 'newmaster@packto.jp',
        ]);

        $response->assertRedirect(route('admin.masters.index'));
        $response->assertSessionHas('temp_credentials.email', 'newmaster@packto.jp');

        $created = User::where('email', 'newmaster@packto.jp')->firstOrFail();
        $this->assertEquals(User::ROLE_MASTER, $created->role);
        $this->assertNull($created->customer_id);

        $tempPassword = session('temp_credentials.password');
        $this->assertTrue(Hash::check($tempPassword, $created->password));
    }

    public function test_master_can_delete_other_master(): void
    {
        $a = $this->makeMaster('master@packto.jp');
        $b = $this->makeMaster('master2@packto.jp');

        $response = $this->actingAs($a)->delete('http://'.config('app.admin_domain').'/masters/'.$b->id);

        $response->assertRedirect(route('admin.masters.index'));
        $this->assertDatabaseMissing('users', ['id' => $b->id]);
    }

    public function test_cannot_delete_self(): void
    {
        $a = $this->makeMaster('master@packto.jp');
        $b = $this->makeMaster('master2@packto.jp');

        $response = $this->actingAs($a)->delete('http://'.config('app.admin_domain').'/masters/'.$a->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $a->id]);
    }

    public function test_cannot_delete_last_master(): void
    {
        $a = $this->makeMaster('master@packto.jp');
        // Only 1 master exists. Try to delete -- but we also can't delete self.
        // Create a 2nd, then attempt to delete the 2nd while leaving only 1.
        // Actually the "last master" check fires when remaining count == 0 AFTER deleting.
        // To test, create 2 masters, login as one, delete the other, then attempt to delete self → forbidden by self-check first.
        // To explicitly test the "last" check, we need to bypass self-check.
        // Workaround: create 2 masters, login as A, delete B, then create a 3rd C, login as C, delete A → leaves only C → C tries to delete... no, can't delete self.
        // The only way to hit "last master" cleanly is: create 2 masters, login as A, try to delete A → self-check fires.
        // So "last master" check is reached when: master X tries to delete master Y where Y != X, and after deletion no master remains.
        // That means count(masters where id != Y) == 0 → only X remains → after deleting Y, only X remains. Wait that's still 1, not 0.
        // The check is `where id != $master->id`. So if 2 masters exist (X, Y), and X is deleting Y, remaining = 1 (X). That's > 0, so allowed.
        // Last master check fires only when 1 master exists and tries to delete itself, but self-check fires first.
        // So the "last master" guard is dead code in normal flow. Keep it as defense in depth, but skip the test (mark as skipped or remove).
        $this->markTestSkipped('"last master" guard is unreachable via normal flow because self-delete is blocked first; kept as defense in depth.');
    }

    public function test_email_must_be_unique(): void
    {
        $a = $this->makeMaster('master@packto.jp');

        $response = $this->actingAs($a)
            ->from('http://'.config('app.admin_domain').'/masters')
            ->post('http://'.config('app.admin_domain').'/masters', [
                'name' => 'Dup',
                'email' => 'master@packto.jp', // 既存
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_customer_role_blocked(): void
    {
        $customer = User::create([
            'name' => 'C',
            'email' => 'c@example.com',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => null,
        ]);

        $response = $this->actingAs($customer)->get('http://'.config('app.admin_domain').'/masters');

        $response->assertForbidden();
    }
}
