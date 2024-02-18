<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_auth_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth', [
            'username' => '123',
            'password' => '123'
        ]);

        $response->assertStatus(422);
    }

    public function test_auth_with_valid_credentials(): void
    {
        User::create([
            'name' => 'test_user',
            'password' => 'merdeka17',
            'email' => 'test@example.com'
        ]);
        
        $response = $this->postJson('/api/auth', [
            'username' => 'test_user',
            'password' => 'merdeka17'
        ]);

        $response->assertStatus(200);
    }
}
