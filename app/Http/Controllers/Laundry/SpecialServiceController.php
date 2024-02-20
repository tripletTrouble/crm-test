<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;
use App\Models\LaundrySpecialService;
use App\Services\LaundryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpecialServiceController extends Controller
{
    public function index(Request $request) 
    {
        if ($request->has('name')) {
            return response()->json(LaundryService::searchSpecialServiceByName($request->name));
        }

        return response()->json(LaundrySpecialService::paginate(10));
    }

    public function store(Request $request)
    {
        $special_service_data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'duration.amount' => ['required', 'numeric', 'max_digits:2'],
            'duration.type' => ['required', 'string', Rule::in(['hour', 'day'])],
            'margin' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:255']
        ]);

        return response()->json(LaundryService::createSpecialService($special_service_data));
    }

    public function update(Request $request, int $id)
    {
        $request->merge(['id' => $id]);
        $special_service_data = $request->validate([
            'id' => ['required', 'numeric', 'exists:laundry_special_services'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'duration.amount' => ['required', 'numeric', 'max_digits:2'],
            'duration.type' => ['required', 'string', Rule::in(['hour', 'day'])],
            'margin' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:255']
        ]);

        return response()->json(LaundryService::updateSpecialService($special_service_data));
    }

    public function find(int $id)
    {
        $special_service = LaundryService::findSpecialService($id);

        if ($special_service) {
            return response()->json($special_service);
        }

        return response()->json(null, 404);
    }

    public function delete(int $id)
    {
        $special_service = LaundryService::deleteSpecialService($id);

        if ($special_service) {
            return response()->json($special_service);
        }

        return response()->json(null, 404);
    }
}
