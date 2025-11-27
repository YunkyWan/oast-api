<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImportadoresTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Creamos/recuperamos rol admin y usuario admin de prueba
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $this->admin = User::factory()->create([
            'email'    => 'admin@test.local',
            'password' => bcrypt('123456'),
        ]);

        $this->admin->roles()->attach($adminRole->id);
    }

    /** @test */
    public function admin_puede_listar_importadores()
    {
        // Creamos un par de filas en la tabla importad "a mano"
        DB::table('importad')->insert([
            [
                'CLAVIM' => 10,
                'NOMIMP' => 'Importador Uno',
                'DNIIMP' => '12345678A',
                // el resto de columnas se rellenan con sus defaults
            ],
            [
                'CLAVIM' => 11,
                'NOMIMP' => 'Importador Dos',
                'DNIIMP' => '87654321B',
            ],
        ]);

        // Actuamos como admin autenticado
        $response = $this->actingAs($this->admin)->getJson('/api/importadores');

        $response->assertStatus(200)
                 ->assertJsonFragment(['NOMIMP' => 'Importador Uno'])
                 ->assertJsonFragment(['NOMIMP' => 'Importador Dos']);
    }

    /** @test */
    public function admin_puede_crear_un_nuevo_importador()
    {
        $payload = [
            'CLAVIM' => 100,
            'NOMIMP' => 'Nuevo Importador Test',
            'DNIIMP' => '11111111H',
            'EORIMP' => 'ES',
            'CALIMP' => '4',
            'DIALMP' => '25',
            'MEALMP' => '11',
            'AÑALMP' => '2025',
            'TELFMP' => '956528007',
            'CORRMP' => 'test@test.es',
            'EORIMP' => 'ES',
            // añade aquí el mínimo de campos que tu controlador marque como required
        ];

        $response = $this->actingAs($this->admin)
                         ->postJson('/api/importadores', $payload);

        // Según tu implementación: 201, 200, 204…
        $response->assertStatus(201);

        $this->assertDatabaseHas('importad', [
            'CLAVIM' => 100,
            'NOMIMP' => 'Nuevo Importador Test',
        ]);
    }

    /** @test */
    public function valida_datos_al_crear_importador()
    {
        // Ejemplo: falta NOMIMP
        $payload = [
            'CLAVIM' => 101,
            'NOMIMP' => '',
            'DNIIMP' => '22222222J',
        ];

        $response = $this->actingAs($this->admin)
                         ->postJson('/api/importadores', $payload);

        $response->assertStatus(422); // Laravel validation error
    }
}
