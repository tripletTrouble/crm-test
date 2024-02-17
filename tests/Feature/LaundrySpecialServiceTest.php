<?php

namespace Tests\Feature;

use App\Services\LaundryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LaundrySpecialServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_create_special_service(): void
    {
        LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 5,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        $this->assertDatabaseHas('laundry_special_services', [
            'name' => 'kilat',
            'margin' => 5/100,
            'duration' => 1*24*60*60
        ]);
    }
    public function test_update_special_service(): void
    {
        $kl = LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 5,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        LaundryService::updateSpecialService([
            'id' => $kl->id,
            'name' => 'Kilat',
            'margin' => 10,
            'description' => 'Description',
            'duration' => [
                'amount' => 12,
                'type' => 'hour'
            ]
        ]);

        $this->assertDatabaseHas('laundry_special_services', [
            'name' => 'kilat',
            'margin' => 10/100,
            'duration' => 12*60*60
        ]);
    }
    public function test_find_special_service(): void
    {
        $kl = LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 5,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        $sp = LaundryService::findSpecialService($kl->id)->toArray();

        $this->assertArrayHasKey('id', $sp);
    }
    public function test_delete_special_service(): void
    {
        $kl = LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 5,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        LaundryService::deleteSpecialService($kl->id);

        $this->assertDatabaseMissing('laundry_special_services', [
            'name' => 'kilat',
            'margin' => 5/100
        ]);
    }
    public function test_search_special_service(): void
    {
        LaundryService::createSpecialService([
            'name' => 'Kilat',
            'margin' => 5,
            'description' => 'Description',
            'duration' => [
                'amount' => 1,
                'type' => 'day'
            ]
        ]);

        $sp = LaundryService::searchSpecialServiceByName('kilat');

        $this->assertEquals(1, count($sp));
    }
}
