<?php

namespace Tests\Feature\Request;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LaundryCustomerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_with_unauthenticated_user(): void
    {
        $response = $this->deleteJson('api/laundry/customers/1');
        $response->assertUnauthorized();

        $response = $this->getJson('api/laundry/customers');
        $response->assertUnauthorized();
    }

    public function test_get_all_customers(): void
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('api/laundry/customers');
        
        $response->assertStatus(200);
    }

    public function test_create_customer_with_invalid_data(): void
    {
        $user = $this->createUser();
        $resp = $this->actingAs($user)->postJson('api/laundry/customers', [
            'name' => 'Agus+====',
        ]);

        $resp->assertUnprocessable();
    }

    public function test_create_customer_with_valid_data(): void
    {
        $user = $this->createUser();
        $resp = $this->createCustomer($user);
        $resp->assertCreated();

        $this->assertDatabaseHas('laundry_customers', [
            'name' => 'agus'
        ]);
    }

    public function test_finding_customer_with_valid_id(): void
    {
        $user = $this->createUser();
        $resp = $this->createCustomer($user);
        $res = $this->actingAs($user)->get('api/laundry/customers/' . $resp['id']);

        $res->assertSee('Agus');
    }

    public function test_finding_customer_with_invalid_id(): void
    {
        $user = $this->createUser();
        $res = $this->actingAs($user)->get('api/laundry/customers/' . '400');

        $res->assertNotFound();
    }

    public function test_delete_customer_with_valid_id(): void
    {
        $user = $this->createUser();
        $resp = $this->createCustomer($user);
        $res = $this->actingAs($user)->delete('api/laundry/customers/' . $resp['id']);

        $res->assertSee('Agus');
    }

    public function test_delete_customer_with_invalid_id(): void
    {
        $user = $this->createUser();
        $res = $this->actingAs($user)->delete('api/laundry/customers/' . '3001');

        $res->assertNotFound();
    }

    public function test_search_customer_by_name(): void
    {
        $user = $this->createUser();
        $this->createCustomer($user);
        $res = $this->actingAs($user)->get('api/laundry/customers?name=agus');

        $res->assertSee('Agus');
    }

    public function test_update_customer_with_valid_data(): void
    {
        $user = $this->createUser();
        $cust = $this->createCustomer($user);
        $res = $this->actingAs($user)->putJson('api/laundry/customers/' . $cust['id'], [
            'name' => 'Budi',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $res->assertSee('Budi');
    }

    public function test_update_customer_with_invalid_id(): void
    {
        $user = $this->createUser();
        $res = $this->actingAs($user)->putJson('api/laundry/customers/' . '4001', [
            'name' => 'Budi',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $res->assertUnprocessable();
    }

    protected function createUser(): User
    {
        return User::create([
            'name' => 'test_user',
            'password' => 'merdeka17',
            'email' => 'test@example.com'
        ]);
    }

    protected function createCustomer(User $user)
    {
        return $this->actingAs($user)->postJson('api/laundry/customers', [
            'name' => 'Agus',
        ]);
    }
}
