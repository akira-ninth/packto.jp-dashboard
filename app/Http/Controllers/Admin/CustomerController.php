<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use App\Services\CloudflareAnalyticsService;
use App\Services\CloudflareKvService;
use App\Support\AuditLogger;
use App\Support\InvitationMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CloudflareKvService $kv,
        private readonly CloudflareAnalyticsService $analytics,
    ) {}

    public function index(): View
    {
        $customers = Customer::with(['plan', 'users'])->orderBy('subdomain')->get();
        $usageMap = $this->analytics->getAllCustomersSummary(30);

        return view('admin.customers.index', [
            'customers' => $customers,
            'usageMap' => $usageMap,
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.create', [
            'plans' => Plan::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subdomain' => ['required', 'string', 'max:63', 'unique:customers,subdomain', 'regex:/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/'],
            'display_name' => ['required', 'string', 'max:255'],
            'origin_url' => ['required', 'url'],
            'plan_id' => ['required', 'exists:plans,id'],
            'active' => ['nullable', 'boolean'],
            'create_user' => ['nullable', 'boolean'],
            'user_email' => ['nullable', 'required_if:create_user,1', 'email', 'unique:users,email'],
            'user_name' => ['nullable', 'required_if:create_user,1', 'string', 'max:255'],
        ]);

        $data['active'] = (bool) ($data['active'] ?? true);
        $data['origin_url'] = rtrim($data['origin_url'], '/');

        $customer = Customer::create([
            'subdomain' => $data['subdomain'],
            'display_name' => $data['display_name'],
            'origin_url' => $data['origin_url'],
            'plan_id' => $data['plan_id'],
            'active' => $data['active'],
        ]);
        $customer->load('plan');
        $this->kv->putCustomer($customer);

        AuditLogger::record('customer.create',
            ['type' => 'customer', 'id' => $customer->id, 'label' => $customer->subdomain],
            ['plan' => $customer->plan->slug, 'origin_url' => $customer->origin_url],
        );

        // 初期ユーザを作成 (オプション)
        if (! empty($data['create_user'])) {
            $tempPassword = Str::password(16, true, true, false);
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'password' => Hash::make($tempPassword),
                'role' => User::ROLE_CUSTOMER,
                'customer_id' => $customer->id,
            ]);

            $mailSent = InvitationMailer::send($user, $tempPassword);

            AuditLogger::record('customer_user.create',
                ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
                ['customer' => $customer->subdomain, 'mail_sent' => $mailSent],
            );

            return redirect()
                ->route('admin.customers.show', $customer)
                ->with('temp_credentials', [
                    'email' => $data['user_email'],
                    'password' => $tempPassword,
                    'mail_sent' => $mailSent,
                ]);
        }

        return redirect()->route('admin.customers.index')->with('status', 'created');
    }

    /**
     * AJAX: subdomain / display_name のユニークチェック
     */
    public function checkUnique(Request $request): \Illuminate\Http\JsonResponse
    {
        $field = $request->input('field'); // 'subdomain' or 'display_name'
        $value = $request->input('value', '');
        $exclude = $request->input('exclude'); // 編集時に自分自身を除外

        if (! in_array($field, ['subdomain', 'display_name'], true) || $value === '') {
            return response()->json(['available' => false]);
        }

        $query = Customer::where($field, $value);
        if ($exclude) {
            $query->where('id', '!=', $exclude);
        }

        return response()->json(['available' => ! $query->exists()]);
    }

    private const ALLOWED_PERIODS = [7, 30, 90];

    public function show(Request $request, Customer $customer): View
    {
        $customer->load('plan', 'users');

        $days = (int) $request->query('days', 7);
        if (! in_array($days, self::ALLOWED_PERIODS, true)) {
            $days = 7;
        }

        return view('admin.customers.show', [
            'customer' => $customer,
            'days' => $days,
            'usageSummary' => $this->analytics->getCustomerSummary($customer->subdomain, $days),
            'usageByDay' => $this->analytics->getCustomerByDay($customer->subdomain, $days),
            'usageByFormat' => $this->analytics->getCustomerByFormat($customer->subdomain, $days),
            'usageByCache' => $this->analytics->getCustomerByCacheStatus($customer->subdomain, $days),
            'largeImages' => $this->analytics->getRecentLargeImages($customer->subdomain, 20),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', [
            'customer' => $customer,
            'plans' => Plan::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'origin_url' => ['required', 'url'],
            'plan_id' => ['required', 'exists:plans,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = (bool) ($data['active'] ?? false);
        $data['origin_url'] = rtrim($data['origin_url'], '/');

        $before = $customer->only(['display_name', 'origin_url', 'plan_id', 'active']);
        $customer->update($data);
        $customer->load('plan');
        $this->kv->putCustomer($customer);

        AuditLogger::record('customer.update',
            ['type' => 'customer', 'id' => $customer->id, 'label' => $customer->subdomain],
            ['before' => $before, 'after' => $customer->only(['display_name', 'origin_url', 'plan_id', 'active'])],
        );

        return redirect()->route('admin.customers.show', $customer)->with('status', 'updated');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $subdomain = $customer->subdomain;
        $customerId = $customer->id;
        $customer->delete();
        $this->kv->deleteCustomer($subdomain);

        AuditLogger::record('customer.delete',
            ['type' => 'customer', 'id' => $customerId, 'label' => $subdomain],
        );

        return redirect()->route('admin.customers.index')->with('status', 'deleted');
    }
}
