<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\CloudflareAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const ALLOWED_PERIODS = [7, 30, 90];

    public function __construct(
        private readonly CloudflareAnalyticsService $analytics,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $customer = $user->customer()->with('plan')->first();

        $days = (int) $request->query('days', 7);
        if (! in_array($days, self::ALLOWED_PERIODS, true)) {
            $days = 7;
        }

        $summary = null;
        $byDay = [];
        $byFormat = [];
        $byCache = [];
        if ($customer) {
            $summary = $this->analytics->getCustomerSummary($customer->subdomain, $days);
            $byDay = $this->analytics->getCustomerByDay($customer->subdomain, $days);
            $byFormat = $this->analytics->getCustomerByFormat($customer->subdomain, $days);
            $byCache = $this->analytics->getCustomerByCacheStatus($customer->subdomain, $days);
        }

        return view('tenant.dashboard', [
            'customer' => $customer,
            'days' => $days,
            'summary' => $summary,
            'byDay' => $byDay,
            'byFormat' => $byFormat,
            'byCache' => $byCache,
        ]);
    }
}
