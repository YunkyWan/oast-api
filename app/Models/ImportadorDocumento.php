<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportadorDocumento extends Model
{
    protected $table = 'importador_documentos';

    protected $fillable = [
        'importador_clavim', 'nombre_original', 'ruta', 'mime', 'tamano'
    ];
}
