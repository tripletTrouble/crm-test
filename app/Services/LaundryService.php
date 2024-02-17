<?php

namespace App\Services;
use App\Models\LaundryBaseRate;
use App\Models\LaundryCustomer;
use App\Models\LaundrySpecialService;
use App\Models\LaundryTransaction;

class LaundryService
{
    public static function createCustomer(array $customer_data): LaundryCustomer
    {
        return LaundryCustomer::create($customer_data);
    }

    public static function updateCustomer(array $customer_data): LaundryCustomer | null
    {
        $customer = LaundryCustomer::find($customer_data['id']);

        if ($customer) {
            foreach ($customer_data as $key => $value) {
                if ($key != 'id') {
                    $customer->$key = $value;
                }
            }
    
            $customer->save();
        }

        return $customer;
    }

    public static function deleteCustomer(int $id): LaundryCustomer | null
    {
        $customer = LaundryCustomer::find($id);
        
        if ($customer) {
            $customer->delete();
        }

        return $customer;
    }

    public static function findCustomer(int $id): LaundryCustomer | null
    {
        return LaundryCustomer::find($id);
    }

    public static function searchCustomerByName(string $name): array
    {
        return LaundryCustomer::where('name', 'like', "%{$name}%")->get()->toArray();
    }

    public static function createBaseRate(array $base_rate_data): LaundryBaseRate
    {
        $base_rate_data['duration'] = self::calculateDuration($base_rate_data['duration']);

        return LaundryBaseRate::create($base_rate_data);
    }

    public static function findBaseRate(int $id): LaundryBaseRate | null
    {
        return LaundryBaseRate::find($id);
    }

    public static function updateBaseRate(array $base_rate_data): LaundryBaseRate | null
    {
        $base_rate = LaundryBaseRate::find($base_rate_data['id']);

        $base_rate_data['duration'] = self::calculateDuration($base_rate_data['duration']);

        if ($base_rate) {
            foreach($base_rate_data as $key => $value) {
                if ($key != 'id') {
                    $base_rate->$key = $value;
                }
            }
    
            $base_rate->save();
        }

        return $base_rate;
    }

    public static function searchBaseRateByName(string $name): array
    {
        return LaundryBaseRate::where('name', 'like', "%{$name}%")->get()->toArray();
    }

    public static function deleteBaseRate(int $id): LaundryBaseRate | null
    {
        $base_rate = LaundryBaseRate::find($id);

        if ($base_rate) {
            $base_rate->delete();
        }

        return $base_rate;
    }

    public static function createSpecialService(array $special_service_data): LaundrySpecialService
    {
        $special_service_data['duration'] = self::calculateDuration($special_service_data['duration']);
        $special_service_data['margin'] /= 100;

        return LaundrySpecialService::create($special_service_data);
    }

    public static function findSpecialService(int $id)
    {
        return LaundrySpecialService::find($id);
    }

    public static function updateSpecialService(array $special_service_data): LaundrySpecialService | null
    {
        $special_service = LaundrySpecialService::find($special_service_data['id']);

        $special_service_data['duration'] = self::calculateDuration($special_service_data['duration']);
        $special_service_data['margin'] /= 100;

        if ($special_service) {
            foreach($special_service_data as $key => $value) {
                if ($key != 'id') {
                    $special_service->$key = $value;
                }
            }

            $special_service->save();
        }

        return $special_service;
    }

    public static function searchSpecialServiceByName(string $name): array | null
    {
        return LaundrySpecialService::where('name', 'like', "%{$name}%")->get()->toArray();
    }

    public static function deleteSpecialService(int $id): LaundrySpecialService | null
    {
        $special_service = LaundrySpecialService::find($id);

        if ($special_service) {
            $special_service->delete();
        }

        return $special_service;
    }

    public static function createTransaction(array $transaction_data): LaundryTransaction | null
    {
        // Get tx lines
        $lines = self::formatLines($transaction_data['lines']);


        unset($transaction_data['lines']);

        // Finding customer
        $cust = LaundryCustomer::find($transaction_data['laundry_customer_id']);

        unset($transaction_data['laundry_customer_id']);

        // Creating transaction
        if ($cust) {
            $transaction = $cust->transactions()->create($transaction_data);
            $transaction->lines()->createMany($lines);

            return $transaction;
        }

        return null;
    }

    public static function updateTransaction(array $transaction_data): LaundryTransaction | null
    {
        // Get the lines
        $lines = self::formatLines($transaction_data['lines']);

        unset($transaction_data['lines']);

        $cust = LaundryCustomer::find($transaction_data['laundry_customer_id']);
        $tx = LaundryTransaction::find($transaction_data['id']);

        if ($cust && $tx) {
            foreach($transaction_data as $key => $value) {
                if ($key != 'key') {
                    $tx->$key = $value;
                }
            }

            $tx->save();
            $tx->lines()->delete();
            $tx->lines()->createMany($lines);

            return $tx;
        }

        return null;
    }

    public static function findTransaction(int $id): LaundryTransaction | null
    {
        return LaundryTransaction::with('lines')->find($id);
    }

    public static function deleteTransaction(int $id): LaundryTransaction | null
    {
        $transaction = LaundryTransaction::find($id);

        if ($transaction) {
            $transaction->lines()->delete();
            $transaction->delete();
        }

        return $transaction;
    }

    public static function findCustomerTransactions(int $customer_id): LaundryCustomer | null
    {
        return LaundryCustomer::with('transactions.lines')->find($customer_id);
    }

    private static function formatLines(array $lines): array
    {
        // Find rates
        $rates = LaundryBaseRate::whereIn('id', array_column($lines, 'laundry_base_rate_id'))->get();

        // Find margins
        $margins = LaundrySpecialService::whereIn('id', array_column($lines, 'laundry_special_service_id'))->get();

        // Calculate charge and duration
        foreach ($lines as &$line) {
            $rate = $rates->first(fn ($item) => $item->id == $line['laundry_base_rate_id']);
            $total = $line['qty'] * $rate->rate;
            $line['duration'] = $rate->duration;

            if (isset($line['laundry_special_service_id'])) {
                $margin = $margins->first(fn ($item) => $item->id == $line['laundry_special_service_id']);
                $charge = $total * $margin->margin;
                $total +=  $charge;
                $line['duration'] = $margin->duration;
                $line['special_service_charge'] = $charge;
            }

            $line['rate'] = $rate->rate;
            $line['sub_total'] = $total;
        }

        return $lines;
    }

    private static function calculateDuration(array $duration): int
    {
        if ($duration['type'] == 'hour') {
            return $duration['amount'] * 60 * 60;
        } else {
            return $duration['amount'] * 60 * 60 * 24;
        }
    }
}
