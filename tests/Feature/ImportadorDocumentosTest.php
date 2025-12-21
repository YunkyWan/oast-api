<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\ImportadorDocumento;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportadorDocumentosTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $consultor;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $consultorRole = Role::firstOrCreate(['name' => 'consultor']);

        // Crear usuarios y asignar roles como en tu proyecto
        $this->admin = User::factory()->create();
        $this->admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->consultor = User::factory()->create();
        $this->consultor->roles()->syncWithoutDetaching([$consultorRole->id]);
    }

    #[Test]
    public function admin_puede_subir_pdf_valido_hasta_10mb(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $file = UploadedFile::fake()->create(
            'documento.pdf',
            1024, // KB (1MB)
            'application/pdf'
        );

        $response = $this->actingAs($this->admin)
            ->postJson('/api/importadores/4/documentos', [
                'file' => $file,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseCount('importador_documentos', 1);

        $doc = ImportadorDocumento::first();
        $this->assertNotNull($doc);
        $this->assertEquals('documento.pdf', $doc->nombre_original);
        $this->assertEquals('application/pdf', $doc->mime);

        Storage::disk('s3')->assertExists($doc->ruta);
    }

    #[Test]
    public function admin_puede_subir_imagen_jpg_y_png(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $jpg = UploadedFile::fake()->image('foto.jpg');
        $png = UploadedFile::fake()->image('logo.png');

        $r1 = $this->actingAs($this->admin)
            ->postJson('/api/importadores/4/documentos', ['file' => $jpg]);
        $r1->assertStatus(201);

        $r2 = $this->actingAs($this->admin)
            ->postJson('/api/importadores/4/documentos', ['file' => $png]);
        $r2->assertStatus(201);

        $this->assertDatabaseCount('importador_documentos', 2);
    }

    #[Test]
    public function se_rechaza_formato_no_permitido(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $bad = UploadedFile::fake()->create('malicioso.txt', 10, 'text/plain');

        $response = $this->actingAs($this->admin)
            ->postJson('/api/importadores/4/documentos', ['file' => $bad]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('importador_documentos', 0);
    }

    #[Test]
    public function se_rechaza_archivo_mayor_de_10mb(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        // 10MB = 10240 KB. Probamos 10241 KB.
        $big = UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf');

        $response = $this->actingAs($this->admin)
            ->postJson('/api/importadores/4/documentos', ['file' => $big]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('importador_documentos', 0);
    }

    #[Test]
    public function consultor_no_puede_subir_documentos(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $file = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->consultor)
            ->postJson('/api/importadores/4/documentos', ['file' => $file]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('importador_documentos', 0);
    }

    #[Test]
    public function usuario_autenticado_puede_listar_documentos(): void
    {
        ImportadorDocumento::create([
            'importador_clavim' => 4,
            'nombre_original' => 'doc.pdf',
            'ruta' => 'importadores/4/doc.pdf',
            'mime' => 'application/pdf',
            'tamano' => 12345,
        ]);

        $response = $this->actingAs($this->consultor)
            ->getJson('/api/importadores/4/documentos');

        $response->assertStatus(200);
        $response->assertJsonFragment(['nombre_original' => 'doc.pdf']);
    }

    #[Test]
    public function view_devuelve_url_firmada(): void
    {
        $doc = ImportadorDocumento::create([
            'importador_clavim' => 4,
            'nombre_original' => 'doc.pdf',
            'ruta' => 'importadores/4/doc.pdf',
            'mime' => 'application/pdf',
            'tamano' => 12345,
        ]);

        // Mock del disk s3 para soportar temporaryUrl sin llamar a AWS real
        $mockDisk = Mockery::mock();
        $mockDisk->shouldReceive('temporaryUrl')
            ->once()
            ->andReturn('https://example.com/temp-view');

        Storage::shouldReceive('disk')
            ->with('s3')
            ->andReturn($mockDisk);

        $response = $this->actingAs($this->consultor)
            ->getJson("/api/documentos/{$doc->id}/view");

        $response->assertStatus(200);
        $response->assertJsonStructure(['url']);
        $response->assertJsonFragment(['url' => 'https://example.com/temp-view']);
    }

    #[Test]
    public function admin_puede_eliminar_documento_y_borra_en_s3(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        Storage::disk('s3')->put('importadores/4/borrar.pdf', 'contenido');

        $doc = ImportadorDocumento::create([
            'importador_clavim' => 4,
            'nombre_original' => 'borrar.pdf',
            'ruta' => 'importadores/4/borrar.pdf',
            'mime' => 'application/pdf',
            'tamano' => 999,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/documentos/{$doc->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('importador_documentos', ['id' => $doc->id]);
        Storage::disk('s3')->assertMissing('importadores/4/borrar.pdf');
    }

    #[Test]
    public function consultor_no_puede_eliminar_documentos(): void
    {
        $doc = ImportadorDocumento::create([
            'importador_clavim' => 4,
            'nombre_original' => 'borrar.pdf',
            'ruta' => 'importadores/4/borrar.pdf',
            'mime' => 'application/pdf',
            'tamano' => 999,
        ]);

        $response = $this->actingAs($this->consultor)
            ->deleteJson("/api/documentos/{$doc->id}");

        $response->assertStatus(403);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
