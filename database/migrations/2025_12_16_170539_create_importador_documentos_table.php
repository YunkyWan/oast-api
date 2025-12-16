<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importador_documentos', function (Blueprint $table) {
            $table->id();

            // Relación con importad.CLAVIM (tabla legada)
            $table->unsignedInteger('importador_clavim');
            $table->string('nombre_original');
            $table->string('ruta');            // clave/ruta dentro del bucket S3
            $table->string('mime', 100);
            $table->unsignedBigInteger('tamano');

            $table->timestamps();

            $table->index('importador_clavim', 'idx_importador_documentos_clavim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importador_documentos');
    }
};
