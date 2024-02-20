<?php

namespace Tests\Feature\Request;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SpecialServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_with_unauthorized_user(): void
    {
        // Index path
        $response = $this->getJson('api/laundry/special-services');
        $response->assertUnauthorized();

        // Store path
        $response = $this->postJson('api/laundry/special-services');
        $response->assertUnauthorized();

        // Update path
        $response = $this->putJson('api/laundry/special-services/1');
        $response->assertUnauthorized();

        // Find path
        $response = $this->getJson('api/laundry/special-services/1');
        $response->assertUnauthorized();

        // Delete path
        $response = $this->deleteJson('api/laundry/special-services/1');
        $response->assertUnauthorized();
    }

    public function test_all_data()
    {
        $response = $this->actingAs($this->create_user())
                        ->getJson('api/laundry/special-services');

        $response->assertSuccessful();
    }

    public function test_create_with_valid_data()
    {
        $response = $this->create_special_service($this->create_user());

        $response->assertSuccessful();
        $this->assertArrayHasKey('id', $response);
    }

    public function test_create_with_invalid_data(): void
    {
        $user = $this->create_user();
        // Invalid name
        $response = $this->actingAs($user)
                    ->postJson('api/laundry/special-services', [
                        'name' => 'Kilat++',
                        'duration' => [
                            'amount' => 6,
                            'type' => 'hour'
                        ],
                        'margin' => 25,
                        'description' => 'Description'
                    ]);

        $response->assertUnprocessable();

        // Invalid duration type
        $response = $this->actingAs($user)
                    ->postJson('api/laundry/special-services', [
                        'name' => 'Kilat',
                        'duration' => [
                            'amount' => 6,
                            'type' => 'minute'
                        ],
                        'margin' => 25,
                        'description' => 'Description'
                    ]);

        $response->assertUnprocessable();

        // Invalid negative margin
        $response = $this->actingAs($user)
                    ->postJson('api/laundry/special-services', [
                        'name' => 'Kilat',
                        'duration' => [
                            'amount' => 6,
                            'type' => 'minute'
                        ],
                        'margin' => -1,
                        'description' => 'Description'
                    ]);

        $response->assertUnprocessable();

        // Invalid zero margin
        $response = $this->actingAs($user)
                    ->postJson('api/laundry/special-services', [
                        'name' => 'Kilat',
                        'duration' => [
                            'amount' => 6,
                            'type' => 'minute'
                        ],
                        'margin' => 0,
                        'description' => 'Description'
                    ]);

        $response->assertUnprocessable();
    }

    public function test_update_with_valid_data(): void
    {
        $user = $this->create_user();

        // Create data
        $data = $this->create_special_service($user);

        // Update data
        $response = $this->actingAs($user)->putJson('api/laundry/special-services/'.$data['id'], [
            'name' => 'Sangat Kilat',
            'duration' => [
                'amount' => 6,
                'type' => 'hour'
            ],
            'margin' => 50,
            'description' => 'Description'
        ]);

        $response->assertSuccessful();
        $response->assertSee(0.5);
        $response->assertSee('Sangat Kilat');
        $response->assertSee(6*60*60);
    }

    public function test_update_with_invalid_data(): void
    {
        $user = $this->create_user();

        // Create data
        $data = $this->create_special_service($user);

        // Invalid id
        $response = $this->actingAs($user)->putJson('api/laundry/special-services/12333', [
            'name' => 'Sangat Kilat',
            'duration' => [
                'amount' => 6,
                'type' => 'hour'
            ],
            'margin' => 50,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();
        
        // Invalid name
        $response = $this->actingAs($user)->putJson('api/laundry/special-services/'.$data['id'], [
            'name' => 'Sangat Kilat++',
            'duration' => [
                'amount' => 6,
                'type' => 'hour'
            ],
            'margin' => 50,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();
        
        // Invalid duration type
        $response = $this->actingAs($user)->putJson('api/laundry/special-services/'.$data['id'], [
            'name' => 'Sangat Kilat',
            'duration' => [
                'amount' => 6,
                'type' => 'second'
            ],
            'margin' => 50,
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();
        
        // Invalid invalid margin
        $response = $this->actingAs($user)->putJson('api/laundry/special-services/'.$data['id'], [
            'name' => 'Sangat Kilat',
            'duration' => [
                'amount' => 6,
                'type' => 'second'
            ],
            'margin' => '50++',
            'description' => 'Description'
        ]);

        $response->assertUnprocessable();
    }

    public function test_find_with_valid_id(): void
    {
        $user = $this->create_user();
        
        // Create data
        $data = $this->create_special_service($user);

        $response = $this->actingAs($user)->getJson('api/laundry/special-services/'.$data['id']);

        $response->assertSuccessful();
        $response->assertSee('Kilat');
        $response->assertSee(6*60*60);
        $response->assertSee(0.25);
    }

    public function test_find_with_invalid_id(): void
    {
        $user = $this->create_user();

        $resposne = $this->actingAs($user)->getJson('api/laundry/special-services/123');

        $resposne->assertNotFound();
    }

    public function test_delete_with_valid_id(): void
    {
        $user = $this->create_user();

        // Create data
        $data = $this->create_special_service($user);

        $response = $this->actingAs($user)->deleteJson('api/laundry/special-services/'.$data['id']);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('laundry_special_services', [
            'name' => 'Kilat'
        ]);
    }

    public function test_delete_with_invalid_id(): void
    {
        $user = $this->create_user();

        $this->create_special_service($user);

        $response = $this->actingAs($user)->deleteJson('api/laundry/special-services/123');

        $response->assertNotFound();
        $this->assertDatabaseHas('laundry_special_services', [
            'name' => 'Kilat'
        ]);
    }

    public function test_search_using_valid_name(): void
    {
        $user = $this->create_user();

        // Create data
        $this->create_special_service($user);

        $response = $this->actingAs($user)->getJson('api/laundry/special-services?name=kilat');

        $response->assertSuccessful();
        $response->assertSee('Kilat');
        $response->assertSee(6*60*60);
    }

    public function test_search_using_invalid_name(): void
    {
        $user = $this->create_user();

        // Create data
        $this->create_special_service($user);

        $response = $this->actingAs($user)->getJson('api/laundry/special-services?name=qwerty');

        $response->assertSuccessful();
        $response->assertDontSee('Kilat');
        $response->assertDontSee(6*60*60);
    }

    private function create_user(): User
    {
        return User::create([
            'name' => 'test_use',
            'email' => 'test@example.com',
            'password' => 'merdeka17'
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
