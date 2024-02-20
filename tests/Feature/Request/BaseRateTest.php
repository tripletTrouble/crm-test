<?php

namespace Tests\Feature\Request;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BaseRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_with_unathenticated_user(): void
    {
        // Index path
        $response = $this->getJson('api/laundry/base-rates');
        $response->assertUnauthorized();

        // Sotore path
        $response = $this->postJson('api/laundry/base-rates');
        $response->assertUnauthorized();

        // Find path
        $response = $this->getJson('api/laundry/base-rates/1');
        $response->assertUnauthorized();

        // Update path
        $response = $this->putJson('api/laundry/base-rates/1');
        $response->assertUnauthorized();

        // Delete path
        $response = $this->deleteJson('api/laundry/base-rates/1');
        $response->assertUnauthorized();
    }

    public function test_create_with_valid_data(): void
    {
        $user = $this->create_user();
        $response = $this->create_base_rate($user);

        $response->assertStatus(200);
    }

    public function test_create_with_invalid_data(): void
    {
        $user = $this->create_user();

        // Invalid name
        $response = $this->actingAs($user)->postJson('api/laundry/base-rates', [
            'name' => '123++123kiloan',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ],
            'rate' => 10_000,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid duration type
        $response = $this->actingAs($user)->postJson('api/laundry/base-rates', [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 1,
                'type' => 'week'
            ],
            'rate' => 10_000,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid duration amount
        $response = $this->actingAs($user)->postJson('api/laundry/base-rates', [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 1_000_000,
                'type' => 'day'
            ],
            'rate' => 10_000,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid rate too much
        $response = $this->actingAs($user)->postJson('api/laundry/base-rates', [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'rate' => 10_000_000_000_000_000,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();
        
        // Invalid rate negative value
        $response = $this->actingAs($user)->postJson('api/laundry/base-rates', [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'rate' => -1,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid rate zero value
        $response = $this->actingAs($user)->postJson('api/laundry/base-rates', [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'rate' => 0,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();
    }

    public function test_update_with_valid_data(): void
    {
        $user = $this->create_user();
        // Create data
        $data = $this->create_base_rate($user);
        $response = $this->actingAs($user)->putJson('api/laundry/base-rates/' . $data['id'], [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'rate' => 12_000,
            'description' => 'Description'
        ]);

        $response->assertSuccessful();
        $response->assertSee(12_000);
    }

    public function test_update_with_invalid_data()
    {
        $user = $this->create_user();
        // Create data
        $data = $this->create_base_rate($user);

        // Invalid id
        $response = $this->actingAs($user)->putJson('/api/laundry/base-rates/'. 3001, [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'rate' => 10_000,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid name
        $response = $this->actingAs($user)->putJson('/api/laundry/base-rates/'. $data['id'], [
            'name' => 'Kiloan++',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'rate' => 10_000,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid duration type
        $response = $this->actingAs($user)->putJson('/api/laundry/base-rates/'. $data['id'], [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'year'
            ],
            'rate' => 10_000,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid too much rate
        $response = $this->actingAs($user)->putJson('/api/laundry/base-rates/'. $data['id'], [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'year'
            ],
            'rate' => 10_000_000_000_000_000,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid too low rate
        $response = $this->actingAs($user)->putJson('/api/laundry/base-rates/'. $data['id'], [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'year'
            ],
            'rate' => -1,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();

        // Invalid zero rate
        $response = $this->actingAs($user)->putJson('/api/laundry/base-rates/'. $data['id'], [
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'year'
            ],
            'rate' => 0,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();
    }

    public function test_find_data(): void
    {
        $user = $this->create_user();
        // Create dsta
        $data = $this->create_base_rate($user);

        $response = $this->actingAs($user)->getJson('api/laundry/base-rates/' . $data['id']);
        $response->assertSuccessful();
        $response->assertSee(10_000);
    }

    public function test_find_with_invalid_id(): void
    {
        $user = $this->create_user();

        $response = $this->actingAs($user)->getJson('api/laundry/base-rates/' . 4001);
        $response->assertNotFound();
    }

    public function test_delete_with_valid_id(): void
    {
        $user = $this->create_user();
        // Create data
        $data = $this->create_base_rate($user);

        $response = $this->actingAs($user)->deleteJson('api/laundry/base-rates/'.$data['id']);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('laundry_base_rates', [
            'name' => 'Kiloan'
        ]);
    }

    public function test_delete_with_invalid_id(): void
    {
        $user = $this->create_user();
        $response = $this->actingAs($user)->deleteJson('api/laundry/base-rates/10001');

        $response->assertNotFound();
    }

    public function test_search_with_valid_base_rate_name(): void
    {
        $user = $this->create_user();
        // Create data
        $this->create_base_rate($user);

        $response = $this->actingAs($user)->getJson('api/laundry/base-rates?name=kiloan');

        $response->assertSuccessful();
        $response->assertSee('Kiloan');
    }

    public function test_search_with_invalid_base_rate_name(): void
    {
        $user = $this->create_user();
        // Create data
        $this->create_base_rate($user);

        $response = $this->actingAs($user)->getJson('api/laundry/base-rates?name=sachet');

        $response->assertSuccessful();
        $response->assertDontSee('Kiloan');
    }

    protected function create_user()
    {
        return User::create([
            'name' => 'test_user',
            'password' => 'merdeka17',
            'email' => 'test@example.com'
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
}
