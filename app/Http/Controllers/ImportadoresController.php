<?php

namespace App\Http\Controllers;

use App\Models\Importador;
use Illuminate\Http\Request;

class ImportadoresController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $query = Importador::query();

        if ($q !== '') {
            // Filtro básico por nombre o DNI (usa like sobre CHAR)
            $query->whereRaw("TRIM(NOMIMP) LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("TRIM(DNIIMP) LIKE ?", ["%{$q}%"]);
        }

        // Puedes paginar si lo prefieres: ->paginate(20)
        $data = $query->orderBy('CLAVIM', 'asc')->limit(200)->get();

        return response()->json($data);
    }

    public function show($id)
    {
        $imp = Importador::findOrFail($id);
        return response()->json($imp);
    }
}
