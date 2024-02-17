<?php

namespace Tests\Feature;

use App\Services\LaundryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LaundryTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_transaction()
    {
        $cs = LaundryService::createCustomer([
            'name' => 'agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $bs = LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        $sp = LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 25,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        LaundryService::createTransaction([
            'laundry_customer_id' => $cs->id,
            'notes' => 'Catatan',
            'lines' => [
                [
                    'laundry_base_rate_id' => $bs->id,
                    'laundry_special_service_id' => $sp->id,
                    'qty' => 2
                ]
            ]
        ]);

        $this->assertDatabaseHas('laundry_transactions', [
            'notes' => 'catatan'
        ]);

        $this->assertDatabaseHas('laundry_transaction_lines', [
            'rate' => 10000,
            'special_service_charge' => (25 / 100) * (10000 * 2),
            'sub_total' => (10000 * 2) + ((25 / 100) * (10000 * 2)),
            'duration' => 1 * 24 * 60 * 60
        ]);
    }

    public function test_find_transaction()
    {
        $cs = LaundryService::createCustomer([
            'name' => 'agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $bs = LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        $sp = LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 25,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        $tx = LaundryService::createTransaction([
            'laundry_customer_id' => $cs->id,
            'notes' => 'Catatan',
            'lines' => [
                [
                    'laundry_base_rate_id' => $bs->id,
                    'laundry_special_service_id' => $sp->id,
                    'qty' => 2
                ]
            ]
        ]);

        $tx2 = LaundryService::findTransaction($tx->id);

        $this->assertNotNull($tx2);
    }

    public function test_delete_transaction()
    {
        $cs = LaundryService::createCustomer([
            'name' => 'agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $bs = LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        $sp = LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 25,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        $tx = LaundryService::createTransaction([
            'laundry_customer_id' => $cs->id,
            'notes' => 'Catatan',
            'lines' => [
                [
                    'laundry_base_rate_id' => $bs->id,
                    'laundry_special_service_id' => $sp->id,
                    'qty' => 2
                ]
            ]
        ]);

        LaundryService::deleteTransaction($tx->id);

        $this->assertDatabaseMissing('laundry_transactions', [
            'notes' => 'Catatan'
        ]);
    }

    public function test_update_transaction()
    {
        $cs = LaundryService::createCustomer([
            'name' => 'agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $bs = LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        $sp = LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 25,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        $tx = LaundryService::createTransaction([
            'laundry_customer_id' => $cs->id,
            'notes' => 'Catatan',
            'lines' => [
                [
                    'laundry_base_rate_id' => $bs->id,
                    'laundry_special_service_id' => $sp->id,
                    'qty' => 2
                ]
            ]
        ]);

        LaundryService::updateTransaction([
            'id' => $tx->id,
            'laundry_customer_id' => $cs->id,
            'notes' => 'Catatan',
            'lines' => [
                [
                    'laundry_base_rate_id' => $bs->id,
                    'laundry_special_service_id' => $sp->id,
                    'qty' => 2
                ],
                [
                    'laundry_base_rate_id' => $bs->id,
                    'qty' => 3
                ]
            ]
        ]);

        $this->assertDatabaseCount('laundry_transaction_lines', 2);

        $this->assertDatabaseHas('laundry_transaction_lines', [
            'laundry_base_rate_id' => $bs->id,
            'qty' => 3,
            'special_service_charge' => null
        ]);
    }
}
