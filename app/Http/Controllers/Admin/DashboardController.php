<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Plan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'customerCount' => Customer::count(),
            'activeCount' => Customer::where('active', true)->count(),
            'planCounts' => Plan::withCount('customers')->get(),
        ]);
    }
}
