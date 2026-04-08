<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $customer = $user->customer()->with('plan')->first();

        return view('tenant.dashboard', [
            'customer' => $customer,
        ]);
    }
}
