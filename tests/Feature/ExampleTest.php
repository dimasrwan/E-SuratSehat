<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseMigrations;

    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->operator = User::create([
            'name' => 'Operator Example Test',
            'email' => 'operator_ex@klinik.uin.ac.id',
            'password' => Hash::make('PasswordOperator123!'),
            'role' => 'operator',
        ]);
        $this->actingAs($this->operator);
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
