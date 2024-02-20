<?php

namespace App\Http\Controllers\Laundry;

use App\Http\Controllers\Controller;
use App\Models\LaundryTransaction;
use App\Services\LaundryService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has(['from','to'])) {
            $request = $request->validate([
                'from' => ['date'],
                'to' => ['date']
            ]);

            $transactions = LaundryTransaction::with('lines.baseRate:name,description,id', 'lines.specialService:name,description,id', 'customer')
                            ->whereDate('created_at', '>=', $request['from'])
                            ->whereDate('created_at', '<=', $request['to'])
                            ->paginate(10);

            return response()->json($transactions);
        }

        return response()->json(LaundryTransaction::with('lines.baseRate:name,description,id', 'lines.specialService:name,description,id', 'customer')->paginate(10));
    }

    public function store(Request $request)
    {
        $transaction_data = $request->validate([
            'laundry_customer_id' => ['required', 'numeric', 'exists:laundry_customers,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'finished_at' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'lines' => ['array'],
            'lines.*.laundry_base_rate_id' => ['required', 'numeric', 'exists:laundry_base_rates,id'],
            'lines.*.laundry_special_service_id' => ['nullable', 'numeric', 'exists:laundry_special_services,id'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0']
        ]);

        return response()->json(LaundryService::createTransaction($transaction_data));
    }

    public function update(Request $request, int $id)
    {
        $request->merge(['id' => $id]);
        $transaction_data = $request->validate([
            'id' => ['required', 'exists:laundry_transactions'],
            'laundry_customer_id' => ['required', 'numeric', 'exists:laundry_customers,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'finished_at' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'lines' => ['array'],
            'lines.*.laundry_base_rate_id' => ['required', 'numeric', 'exists:laundry_base_rates,id'],
            'lines.*.laundry_special_service_id' => ['nullable', 'numeric', 'exists:laundry_special_services,id'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0']
        ]);

        return response()->json(LaundryService::updateTransaction($transaction_data));
    }

    public function find(int $id)
    {
        $transaction = LaundryService::findTransaction($id);

        if ($transaction) {
            return response()->json($transaction);
        }

        return response()->json(null, 404);
    }

    public function delete(int $id)
    {
        $transaction = LaundryService::deleteTransaction($id);

        if ($transaction) {
            return response()->json($transaction);
        }

        return response()->json(null, 404);
    }
}
