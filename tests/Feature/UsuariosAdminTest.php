<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuariosAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $consultor;
    protected Role $adminRole;
    protected Role $consultorRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Aseguramos que existen los roles sin duplicar registros
        $this->adminRole     = Role::firstOrCreate(['name' => 'admin']);
        $this->consultorRole = Role::firstOrCreate(['name' => 'consultor']);

        // Usuario admin de prueba
        $this->admin = User::factory()->create([
            'email'    => 'admin-test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Usuario consultor de prueba
        $this->consultor = User::factory()->create([
            'email'    => 'consultor-test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Asignar roles evitando duplicados en la tabla pivote
        $this->admin->roles()->syncWithoutDetaching([$this->adminRole->id]);
        $this->consultor->roles()->syncWithoutDetaching([$this->consultorRole->id]);
    }

    #[Test]
    public function admin_puede_listar_usuarios(): void
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/usuarios');

        $response->assertStatus(200);
        // $response->assertJsonStructure([['id', 'name', 'email']]);
    }

    #[Test]
    public function consultor_no_puede_listar_usuarios(): void
    {
        $response = $this->actingAs($this->consultor)
                         ->getJson('/api/usuarios');

        $response->assertStatus(403);
    }

    #[Test]
    public function invitado_no_puede_listar_usuarios(): void
    {
        $response = $this->getJson('/api/usuarios');

        $response->assertStatus(401);
    }
}
