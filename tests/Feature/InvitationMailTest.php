<?php

namespace Tests\Feature;

use App\Mail\UserInvitationMail;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Phase 13j: 招待メール送信を Mail::fake で検証。
 *
 * 3 経路 (CustomerController, CustomerUserController, MasterController) を網羅。
 */
class InvitationMailTest extends TestCase
{
    use RefreshDatabase;

    private function makeMaster(): User
    {
        return User::create([
            'name' => 'Master',
            'email' => 'master@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_MASTER,
            'customer_id' => null,
        ]);
    }

    private function makeProPlan(): Plan
    {
        return Plan::create([
            'slug' => 'pro',
            'name' => 'Pro',
            'features' => ['image' => true, 'text' => true],
        ]);
    }

    public function test_customer_create_with_user_sends_invitation_mail(): void
    {
        Mail::fake();
        $pro = $this->makeProPlan();
        $master = $this->makeMaster();

        $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/customers', [
                'subdomain' => 'newco',
                'display_name' => 'New Co',
                'origin_url' => 'https://newco.example.com',
                'plan_id' => $pro->id,
                'create_user' => '1',
                'user_name' => 'NewCo Admin',
                'user_email' => 'admin@newco.example.com',
            ]);

        Mail::assertSent(UserInvitationMail::class, function ($mail) {
            return $mail->hasTo('admin@newco.example.com')
                && $mail->user->email === 'admin@newco.example.com'
                && $mail->user->role === User::ROLE_CUSTOMER
                && str_contains($mail->loginUrl, config('app.app_domain'))
                && strlen($mail->tempPassword) >= 16;
        });
    }

    public function test_customer_user_add_sends_invitation_mail(): void
    {
        Mail::fake();
        $pro = $this->makeProPlan();
        $customer = Customer::create([
            'subdomain' => 'rays-hd',
            'display_name' => 'rays-hd',
            'origin_url' => 'https://rays-hd.com',
            'plan_id' => $pro->id,
            'active' => true,
        ]);
        $master = $this->makeMaster();

        $this->actingAs($master)
            ->post('http://'.config('app.admin_domain')."/customers/{$customer->id}/users", [
                'name' => 'Second',
                'email' => 'second@rays-hd.example',
            ]);

        Mail::assertSent(UserInvitationMail::class, function ($mail) {
            return $mail->hasTo('second@rays-hd.example');
        });
    }

    public function test_master_add_sends_invitation_mail_with_admin_login_url(): void
    {
        Mail::fake();
        $master = $this->makeMaster();

        $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/masters', [
                'name' => 'New Master',
                'email' => 'new-master@packto.jp',
            ]);

        Mail::assertSent(UserInvitationMail::class, function ($mail) {
            return $mail->hasTo('new-master@packto.jp')
                && $mail->user->role === User::ROLE_MASTER
                && str_contains($mail->loginUrl, config('app.admin_domain'));
        });
    }

    public function test_invitation_mail_renders_temp_password_in_body(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('whatever'),
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => null,
        ]);
        $mail = new UserInvitationMail($user, 'temp-pass-1234ABCD', 'https://app.packto.jp/login');

        $rendered = $mail->render();
        $this->assertStringContainsString('temp-pass-1234ABCD', $rendered);
        $this->assertStringContainsString('test@example.com', $rendered);
        $this->assertStringContainsString('https://app.packto.jp/login', $rendered);
        $this->assertStringContainsString('Test User', $rendered);
    }
}
