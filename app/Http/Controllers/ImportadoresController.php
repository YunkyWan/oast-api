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

    public function store(Request $r)
    {
        // Validación mínima (puedes ampliar reglas según tu negocio)
        $rules = [
            'CLAVIM' => 'required|integer|min:1|unique:importad,CLAVIM',
            'NOMIMP' => 'required|string|max:30',
            'DNIIMP' => 'nullable|string|max:9',
            'EORIMP' => 'nullable|string|max:20',
            'EXEIMP' => 'nullable|in:S,N,',
            'PAGIMP' => 'nullable|in:S,N,',
            'TELFMP' => 'nullable|integer|min:0',
            'TELMMP' => 'nullable|integer|min:0',
            'CORRMP' => 'nullable|email|max:30',
            'CALIMP' => 'nullable|integer|min:0',
            'NUMIMP' => 'nullable|string|max:5',
            'CALAMP' => 'nullable|integer|min:0',
            'NUMAMP' => 'nullable|string|max:5',
            'NOMRAP' => 'nullable|string|max:30',
            'DNIRAP' => 'nullable|string|max:9',
            'TIREMP' => 'nullable|string|max:1',
            'TITUMP' => 'nullable|string|max:25',
            'CALRAP' => 'nullable|integer|min:0',
            'NUMRAP' => 'nullable|string|max:5',
            'DENCMP' => 'nullable|string|max:30',
            'OBSEMP' => 'nullable|string|max:60',
            'BLO1MP' => 'nullable|string|max:2',
            'POR1MP' => 'nullable|string|max:2',
            'BLO2MP' => 'nullable|string|max:2',
            'POR2MP' => 'nullable|string|max:2',
            'BLO3MP' => 'nullable|string|max:2',
            'POR3MP' => 'nullable|string|max:2',
            'DIALMP' => 'nullable|integer|min:0|max:31',
            'MEALMP' => 'nullable|integer|min:0|max:12',
            'AÑALMP' => 'nullable|integer|min:0|max:9999',
        ];
        $data = $r->validate($rules);

        $imp = new \App\Models\Importador();

        // asignación segura campo a campo (incluyendo claves con "Ñ")
        foreach ($data as $k => $v) {
            $imp->setAttribute($k, $v === null ? '' : $v);
        }

        // poner por defecto '' o 0 en las columnas no informadas
        foreach ($imp->getAttributes() as $k => $v) {
            if ($v === null) {
                // ints/counters a 0; chars a ''
                $imp->setAttribute($k, is_int($v) ? 0 : '');
            }
        }

        $imp->save();

        return response()->json(['ok' => true, 'id' => $imp->CLAVIM], 201);
    }

}
