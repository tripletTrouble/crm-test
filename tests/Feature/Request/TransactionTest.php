<?php

namespace Tests\Feature\Request;

use App\Models\LaundryTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    public function test_with_unauthorized_user(): void
    {
        // Index path
        $response = $this->getJson('api/laundry/transactions');

        $response->assertUnauthorized();

        // Store path
        $response = $this->postJson('api/laundry/transactions');

        $response->assertUnauthorized();

        // Update path
        $response = $this->putJson('api/laundry/transactions/1');

        $response->assertUnauthorized();

        // Find path
        $response = $this->getJson('api/laundry/transactions/1');

        $response->assertUnauthorized();

        // Delete path
        $response = $this->deleteJson('api/laundry/transactions/1');

        $response->assertUnauthorized();
    }

    public function test_create_with_valid_data(): void
    {
        $user = $this->create_user();

        $res = $this->create_transaction($user);

        $response = $res['transaction_plain'];

        $response->assertSuccessful();
        $this->assertDatabaseHas('laundry_transaction_lines', [
            'sub_total' => 5*10_000,
            'special_service_charge' => null,
            'rate' => 10_000
        ]);

        $response = $res['transaction_special'];

        $response->assertSuccessful();
        $this->assertDatabaseHas('laundry_transaction_lines', [
            'sub_total' => 5*10_000+(50_000*0.25),
            'special_service_charge' => (50_000*0.25),
            'rate' => 10_000
        ]);
    }

    public function test_create_transaction_with_invalid_data(): void
    {
        $user = $this->create_user();
        $cust = $this->createCustomer($user);
        $rate = $this->create_base_rate($user);
        $special = $this->create_special_service($user);
        
        // Invalid customer_id
        $response = $this->actingAs($user)->postJson('api/laundry/transactions', [
            'laundry_customer_id' => 14015,
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $rate['id'],
                    'laundry_special_service_id' => null,
                    'qty' => 5
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid base_rate_id
        $response = $this->actingAs($user)->postJson('api/laundry/transactions', [
            'laundry_customer_id' => $cust['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => 1776,
                    'laundry_special_service_id' => null,
                    'qty' => 5
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid special_service_id
        $response = $this->actingAs($user)->postJson('api/laundry/transactions', [
            'laundry_customer_id' => $cust['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $rate['id'],
                    'laundry_special_service_id' => 19991,
                    'qty' => 5
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid negative qty
        $response = $this->actingAs($user)->postJson('api/laundry/transactions', [
            'laundry_customer_id' => $cust['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $rate['id'],
                    'laundry_special_service_id' => null,
                    'qty' => -1
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid zero qty
        $response = $this->actingAs($user)->postJson('api/laundry/transactions', [
            'laundry_customer_id' => $cust['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $rate['id'],
                    'laundry_special_service_id' => null,
                    'qty' => 0
                ]
            ]
        ]);

        $response->assertUnprocessable();
    }

    public function test_find_with_valid_id(): void
    {
        $user = $this->create_user();
        
        // Creating data
        $dt = $this->create_transaction($user);

        $response = $this->actingAs($user)->getJson('api/laundry/transactions/'.$dt['transaction_plain']['id']);

        $response->assertSuccessful();
        $response->assertSee('Agus');
    }

    public function test_find_with_invalid_id(): void
    {
        $user = $this->create_user();
        
        // Creating data
        $dt = $this->create_transaction($user);

        $response = $this->actingAs($user)->getJson('api/laundry/transactions/'.'1945');

        $response->assertNotFound();
    }

    public function test_update_with_valid_data(): void
    {
        $user = $this->create_user();

        $dt = $this->create_transaction($user);

        $response = $this->actingAs($user)->putJson('api/laundry/transactions/'.$dt['transaction_plain']['id'], [
            'laundry_customer_id' => $dt['customer']['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $dt['base_rate']['id'],
                    'laundry_special_service_id' => $dt['special_service']['id'],
                    'qty' => 8
                ]
            ]
        ]);

        $response->assertSuccessful();
        $response->assertSee(['Agus', 10_000, 8*10_000*0.25, (8*10_000*0.25)+(8*10_000)]);
    }

    public function test_update_with_invalid_data(): void
    {
        $user = $this->create_user();
        $dt = $this->create_transaction($user);

        // Invalid id
        $response = $this->actingAs($user)->putJson('api/laundry/transactions/1234', [
            'laundry_customer_id' => $dt['customer']['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $dt['base_rate']['id'],
                    'laundry_special_service_id' => $dt['special_service']['id'],
                    'qty' => 8
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid customer id
        $response = $this->actingAs($user)->putJson('api/laundry/transactions/'.$dt['transaction_plain']['id'], [
            'laundry_customer_id' => 12345,
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $dt['base_rate']['id'],
                    'laundry_special_service_id' => $dt['special_service']['id'],
                    'qty' => 8
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid base_rate id
        $response = $this->actingAs($user)->putJson('api/laundry/transactions/'.$dt['transaction_plain']['id'], [
            'laundry_customer_id' => $dt['customer']['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => 1234,
                    'laundry_special_service_id' => $dt['special_service']['id'],
                    'qty' => 8
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid special_service id
        $response = $this->actingAs($user)->putJson('api/laundry/transactions/'.$dt['transaction_plain']['id'], [
            'laundry_customer_id' => $dt['customer']['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $dt['base_rate']['id'],
                    'laundry_special_service_id' => 1234,
                    'qty' => 8
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid negative qty
        $response = $this->actingAs($user)->putJson('api/laundry/transactions/'.$dt['transaction_plain']['id'], [
            'laundry_customer_id' => $dt['customer']['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $dt['base_rate']['id'],
                    'laundry_special_service_id' => $dt['special_service']['id'],
                    'qty' => -1
                ]
            ]
        ]);

        $response->assertUnprocessable();

        // Invalid zero qty
        $response = $this->actingAs($user)->putJson('api/laundry/transactions/'.$dt['transaction_plain']['id'], [
            'laundry_customer_id' => $dt['customer']['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $dt['base_rate']['id'],
                    'laundry_special_service_id' => $dt['special_service']['id'],
                    'qty' => 0
                ]
            ]
        ]);

        $response->assertUnprocessable();
    }

    public function test_delete_with_valid_id(): void
    {
        $user = $this->create_user();

        // Creating data
        $dt = $this->create_transaction($user);

        $response = $this->actingAs($user)->deleteJson('api/laundry/transactions/'.$dt['transaction_plain']['id']);

        $response->assertSuccessful();

        $this->assertDatabaseCount('laundry_transactions', 1);
        $this->assertDatabaseCount('laundry_transaction_lines', 1);
    }

    public function test_delete_with_invalid_id(): void
    {
        $user = $this->create_user();

        // Creating data
        $dt = $this->create_transaction($user);

        $response = $this->actingAs($user)->deleteJson('api/laundry/transactions/15677');

        $response->assertNotFound();

        $this->assertDatabaseCount('laundry_transactions', 2);
        $this->assertDatabaseCount('laundry_transaction_lines', 2);
    }

    public function test_filter_with_valid_date()
    {
        $user = $this->create_user();

        // Creating data
        $dt = $this->create_transaction($user);

        $response = $this->actingAs($user)->getJson('api/laundry/transactions?from=2024-02-19&to=2024-02-19');

        $response->assertSuccessful();
        $response->assertSee(['Agus', 'Kilat', 'Kiloan']);
    }

    public function test_filter_with_invalid_date()
    {
        $user = $this->create_user();

        // Creating data
        $dt = $this->create_transaction($user);

        $response = $this->actingAs($user)->getJson('api/laundry/transactions?from=asd&to=asd');

        $response->assertUnprocessable();
    }

    public function test_filter_with_wrong_date()
    {
        $user = $this->create_user();

        // Creating data
        $dt = $this->create_transaction($user);

        $response = $this->actingAs($user)->getJson('api/laundry/transactions?from=2023-01-01d&to=2023-02-01');

        $response->assertSuccessful();
        $response->assertDontSee(['Agus', 'Kilat', 'Kiloan']);
    }

    protected function create_user(): User
    {
        return User::create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => 'merdeka17'
        ]);
    }

    protected function create_transaction(User $user): array
    {
        $cust = $this->createCustomer($user);
        $rate = $this->create_base_rate($user);
        $special = $this->create_special_service($user);
        $tx_1 = $this->actingAs($user)->postJson('api/laundry/transactions', [
            'laundry_customer_id' => $cust['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $rate['id'],
                    'laundry_special_service_id' => null,
                    'qty' => 5
                ]
            ]
        ]);
        $tx_2 = $this->actingAs($user)->postJson('api/laundry/transactions', [
            'laundry_customer_id' => $cust['id'],
            'notes' => 'Notes',
            'lines' => [
                [
                    'laundry_base_rate_id' => $rate['id'],
                    'laundry_special_service_id' => $special['id'],
                    'qty' => 5
                ]
            ]
        ]);

        return [
            'customer' => $cust,
            'base_rate' => $rate,
            'special_service' => $special,
            'transaction_plain' => $tx_1,
            'transaction_special' => $tx_2
        ];
    }

    protected function createCustomer(User $user)
    {
        return $this->actingAs($user)->postJson('api/laundry/customers', [
            'name' => 'Agus',
        ]);
    }

    protected function create_base_rate(User $user)
    {
        return $this->actingAs($user)->postJson('/api/laundry/base-rates', [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'rate' => 10_000,
            'description' => 'Description'
        ]);
    }

    private function create_special_service(User $user)
    {
        return $this->actingAs($user)->postJson('api/laundry/special-services', [
            'name' => 'Kilat',
            'duration' => [
                'amount' => 6,
                'type' => 'hour'
            ],
            'margin' => 25
        ]);
    }
}
