<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertNoContent(); // 204 en Breeze API
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Act: intentamos login con contraseña incorrecta
        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        // Assert: debe redirigir de vuelta al login con errores
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        // El usuario debe seguir sin autenticarse
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertNoContent(); // 204 en Breeze API
    }
}
