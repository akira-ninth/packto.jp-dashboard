<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\CloudflareAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly CloudflareAnalyticsService $analytics,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $customer = $user->customer()->with('plan')->first();

        $summary = null;
        $byDay = [];
        $byFormat = [];
        $byCache = [];
        if ($customer) {
            $summary = $this->analytics->getCustomerSummary($customer->subdomain, 7);
            $byDay = $this->analytics->getCustomerByDay($customer->subdomain, 7);
            $byFormat = $this->analytics->getCustomerByFormat($customer->subdomain, 7);
            $byCache = $this->analytics->getCustomerByCacheStatus($customer->subdomain, 7);
        }

        return view('tenant.dashboard', [
            'customer' => $customer,
            'summary' => $summary,
            'byDay' => $byDay,
            'byFormat' => $byFormat,
            'byCache' => $byCache,
        ]);
    }
}
