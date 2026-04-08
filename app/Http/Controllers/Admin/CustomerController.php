<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('admin.customers.index', [
            'customers' => Customer::with('plan')->orderBy('subdomain')->get(),
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
        ]);

        $data['active'] = (bool) ($data['active'] ?? true);

        Customer::create($data);

        return redirect()->route('admin.customers.index')->with('status', 'created');
    }

    public function show(Customer $customer): View
    {
        $customer->load('plan', 'users');

        return view('admin.customers.show', ['customer' => $customer]);
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

        $customer->update($data);

        return redirect()->route('admin.customers.show', $customer)->with('status', 'updated');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('status', 'deleted');
    }
}
