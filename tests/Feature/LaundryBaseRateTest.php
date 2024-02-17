<?php

namespace Tests\Feature;

use App\Services\LaundryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LaundryBaseRateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating base rate
     */
    public function test_create_base_rate(): void
    {
        LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        LaundryService::createBaseRate([
            'name' => 'Dry Cleaning',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 15000
        ]);

        $this->assertDatabaseHas('laundry_base_rates', [
            'name' => 'kiloan',
            'duration' => 2*24*60*60
        ]);

        $this->assertDatabaseHas('laundry_base_rates', [
            'name' => 'dry cleaning',
            'duration' => 2*24*60*60
        ]);
    }

    public function test_update_base_rate(): void
    {
        $kl = LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        LaundryService::updateBaseRate([
            'id' => $kl->id,
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 3,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        $this->assertDatabaseHas('laundry_base_rates', [
            'name' => 'kiloan',
            'duration' => 3*24*60*60
        ]);
    }

    public function test_find_base_rate(): void
    {
        $kl = LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        $bs = LaundryService::findBaseRate($kl->id)->toArray();

        $this->assertArrayHasKey('id', $bs);
    }

    public function test_delete_base_rate(): void
    {
        $kl = LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        LaundryService::deleteBaseRate($kl->id);

        $this->assertDatabaseMissing('laundry_base_rates', [
            'name' => 'kiloan',
            'duration' => 3*24*60*60
        ]);
    }

    public function test_search_base_rate(): void
    {
        $kl = LaundryService::createBaseRate([
            'name' => 'Kiloan',
            'duration' => [
                'amount' => 2,
                'type' => 'day'
            ],
            'description' => 'Description',
            'rate' => 10000
        ]);

        $bs = LaundryService::searchBaseRateByName('kiloan');

        $this->assertEquals(1, count($bs));
    }
}
