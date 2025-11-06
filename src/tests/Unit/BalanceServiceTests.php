<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class BalanceServiceTests extends TestCase
{
    use RefreshDatabase;
    
    public function test_balance()
    {
        $user = User::factory()->create(['balance' => 50]);
        
        $response = $this->postJson('/api/balance', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['balance']); 

        $this->assertGreaterThan( 0, $user->fresh()->balance);
    }   

    public function test_balance_validation_error()
    {
        $response = $this->postJson('/api/balance', ['user_id' => "кролль"]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['error']);
    }
    public function test_balance_existence_error()
    {
        $response = $this->postJson('/api/balance', ['user_id' => 9999999999999]);

        $response->assertStatus(404)
                 ->assertJsonStructure(['error']);
    }

}