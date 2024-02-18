<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;
use App\Models\LaundryCustomer;
use App\Services\LaundryService;
use Illuminate\Http\Request;

class CunstomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('name')){
            $res = LaundryService::searchCustomerByName($request->get('name'));

            return response()->json($res);
        }

        return response()->json(LaundryCustomer::paginate(10));
    }

    public function store(Request $request)
    {
        $customer_data = $request->validate([
            'name' => ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/', 'max:255'],
            'phone' => ['nullable', 'numeric', 'max_digits:30'],
            'address' => ['nullable', 'string', 'max:255']
        ]);

        return LaundryService::createCustomer($customer_data);
    }

    public function update(Request $request, string $id)
    {
        $request->merge(['id' => $id]);

        $customer_data = $request->validate([
            'id' => ['required', 'numeric', 'exists:laundry_customers'],
            'name' => ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/', 'max:255'],
            'phone' => ['nullable', 'numeric', 'max_digits:30'],
            'address' => ['nullable', 'string', 'max:255']
        ]);

        return LaundryService::updateCustomer($customer_data);
    }

    public function find(string $id)
    {
        $cust = LaundryService::findCustomer($id);

        if ($cust) {
            return response()->json($cust);
        }

        return response()->json(null, 404);
    }

    public function delete(string $id)
    {
        $cust = LaundryService::deleteCustomer($id);

        if ($cust) {
            return response()->json($cust);
        }

        return response()->json(null, 404);
    }
}
