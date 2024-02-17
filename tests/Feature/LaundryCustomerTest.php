<?php

namespace Tests\Feature;

use App\Services\LaundryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LaundryCustomerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_creating_customer(): void
    {
        LaundryService::createCustomer([
            'name' => 'Agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $this->assertDatabaseHas('laundry_customers', [
            'name' => 'Agus'
        ]);
    }

    public function test_finding_customer(): void
    {
        $customer = LaundryService::createCustomer([
            'name' => 'Agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $customer = LaundryService::findCustomer($customer->id)->toArray();

        $this->assertArrayHasKey('id', $customer);
    }

    public function test_updating_customer(): void
    {
        $customer = LaundryService::createCustomer([
            'name' => 'Agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $customer = $customer->toArray();
        $customer['name'] = 'Budi';

        LaundryService::updateCustomer($customer);

        $this->assertDatabaseHas('laundry_customers', [
            'name' => 'Budi'
        ]);
    }

    public function test_delete_customer(): void
    {
        $customer = LaundryService::createCustomer([
            'name' => 'Agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        LaundryService::deleteCustomer($customer->id);

        $this->assertDatabaseMissing('laundry_customers', [
            'name' => 'Agus'
        ]);
    }

    public function test_searching_customer(): void
    {
        LaundryService::createCustomer([
            'name' => 'Agus',
            'address' => 'Yogyakarta',
            'phone' => '081234567890'
        ]);

        $customers = LaundryService::searchCustomerByName('agus');

        $this->assertEquals(count($customers), 1);
    }
}
