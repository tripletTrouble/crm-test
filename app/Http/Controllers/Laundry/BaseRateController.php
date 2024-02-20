<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;
use App\Models\LaundryBaseRate;
use App\Services\LaundryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BaseRateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('name')) {
            return response()->json(LaundryService::searchBaseRateByName($request->name));
        }

        return response()->json(LaundryBaseRate::paginate(10));
    }

    public function find(int $id)
    {
        $rate =  LaundryService::findBaseRate($id);

        if ($rate) {
            return response()->json($rate);
        }

        return response()->json(null, 404);
    }

    public function update(Request $request, int $id)
    {
        $request->merge(['id' => $id]);
        $base_rate_data = $request->validate([
            'id' => ['required', 'exists:laundry_base_rates'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'duration.amount' => ['required', 'numeric', 'max_digits:2'],
            'duration.type' => ['required', 'string', Rule::in(['hour', 'day'])],
            'description' => ['nullable', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'max:'.((2**31)-1), 'gt:0']
        ]);

        return response()->json(LaundryService::updateBaseRate($base_rate_data));
    }

    public function store(Request $request)
    {
        $base_rate_data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'duration.amount' => ['required', 'numeric', 'max_digits:2'],
            'duration.type' => ['required', 'string', Rule::in(['hour', 'day'])],
            'description' => ['nullable', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'max:'.((2**31)-1), 'gt:0']
        ]);

        return response()->json(LaundryService::createBaseRate($base_rate_data));
    }

    public function delete(int $id)
    {
        $base_rate = LaundryService::deleteBaseRate($id);

        if ($base_rate) {
            return response()->json($base_rate);
        }

        return response()->json(null, 404);
    }
}
