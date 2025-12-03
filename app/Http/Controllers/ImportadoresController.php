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

    public function store(Request $request)
    {
        // 1. Validación de entrada
        $data = $request->validate([
            // Campos obligatorios
            'NOMIMP' => ['required', 'string', 'max:255'],
            'DNIIMP' => ['required', 'string', 'max:50'],
            'NOMRAP' => ['required', 'string', 'max:255'],
            'DNIRAP' => ['required', 'string', 'max:50'],
            'CORRMP' => ['required', 'email', 'max:255'],

            // Campos opcionales
            'EORIMP' => ['nullable', 'string', 'max:50'],
            'EXEIMP' => ['nullable', 'string', 'max:1'],
            'PAGIMP' => ['nullable', 'string', 'max:1'],

            'TELFMP' => ['nullable', 'integer'],
            'TELMMP' => ['nullable', 'integer'],

            // CALIMP smallint (0–32767 aprox.)
            'CALIMP'  => ['nullable', 'integer', 'between:0,32767'],
            'NUMIMP'  => ['nullable', 'string', 'max:20'],
            'BLO1MP'  => ['nullable', 'string', 'max:20'],
            'POR1MP'  => ['nullable', 'string', 'max:20'],

            'TIREMP' => ['nullable', 'string', 'max:50'],
            'TITUMP' => ['nullable', 'string', 'max:50'],

            'DENCMP' => ['nullable', 'string', 'max:255'],
            'OBSEMP' => ['nullable', 'string', 'max:255'],

            // Fecha descompuesta (ya viene calculada desde el frontend)
            'DIALMP' => ['nullable', 'integer', 'between:1,31'],
            'MEALMP' => ['nullable', 'integer', 'between:1,12'],
            'AÑALMP' => ['nullable', 'integer', 'between:1900,2100'],
        ]);

        // 2. Generar la clave CLAVIM (PK) de forma automática
        //    Como la tabla es legada y no tiene AUTO_INCREMENT,
        //    calculamos max(CLAVIM) + 1.
        $nextId = Importador::max('CLAVIM');
        $nextId = $nextId ? $nextId + 1 : 1; // si la tabla está vacía

        $imp = new Importador();
        $imp->CLAVIM = $nextId;

        // 3. Rellenar el resto de campos permitidos
        $imp->fill($data);

        // 4. Opcional: normalizar nulls -> '' o 0 según tu política
        //    OJO: el código que tenías con is_int($v) sobre null
        //    nunca iba a entrar por el caso int. Si quieres mantener
        //    esta lógica, te recomiendo algo más explícito:
        foreach ($imp->getAttributes() as $k => $v) {
            if ($v === null) {
                // Teléfonos y claves numéricas -> 0
                if (in_array($k, ['TELFMP', 'TELMMP', 'CALIMP', 'DIALMP', 'MEALMP', 'AÑALMP'], true)) {
                    $imp->setAttribute($k, 0);
                } else {
                    // Resto (casi todo CHAR en IMPORTAD) -> cadena vacía
                    $imp->setAttribute($k, '');
                }
            }
        }

        $imp->save();

        return response()->json([
            'ok' => true,
            'id' => $imp->CLAVIM,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // 1. Buscar el importador por su clave primaria (CLAVIM)
        $imp = Importador::find($id);

        if (! $imp) {
            return response()->json([
                'message' => 'Importador no encontrado.',
            ], 404);
        }

        // 2. Validación (mismas reglas que en store)
        $data = $request->validate([
            // Campos obligatorios
            'NOMIMP' => ['required', 'string', 'max:255'],
            'DNIIMP' => ['required', 'string', 'max:50'],
            'NOMRAP' => ['required', 'string', 'max:255'],
            'DNIRAP' => ['required', 'string', 'max:50'],
            'CORRMP' => ['required', 'email', 'max:255'],

            // Campos opcionales
            'EORIMP' => ['nullable', 'string', 'max:50'],
            'EXEIMP' => ['nullable', 'string', 'max:1'],
            'PAGIMP' => ['nullable', 'string', 'max:1'],

            'TELFMP' => ['nullable', 'integer'],
            'TELMMP' => ['nullable', 'integer'],

            // CALIMP smallint (0–32767 aprox.)
            'CALIMP'  => ['nullable', 'integer', 'between:0,32767'],
            'NUMIMP'  => ['nullable', 'string', 'max:20'],
            'BLO1MP'  => ['nullable', 'string', 'max:20'],
            'POR1MP'  => ['nullable', 'string', 'max:20'],

            'TIREMP' => ['nullable', 'string', 'max:50'],
            'TITUMP' => ['nullable', 'string', 'max:50'],

            'DENCMP' => ['nullable', 'string', 'max:255'],
            'OBSEMP' => ['nullable', 'string', 'max:255'],

            // Fecha descompuesta
            'DIALMP' => ['nullable', 'integer', 'between:1,31'],
            'MEALMP' => ['nullable', 'integer', 'between:1,12'],
            'AÑALMP' => ['nullable', 'integer', 'between:1900,2100'],
        ]);

        // 3. Rellenar los campos del modelo (CLAVIM no está en $fillable, por tanto no se modifica)
        $imp->fill($data);

        // 4. Normalizar nulls según política de tu tabla legada
        foreach ($imp->getAttributes() as $k => $v) {
            if ($v === null) {
                // Campos numéricos → 0
                if (in_array($k, ['TELFMP', 'TELMMP', 'CALIMP', 'DIALMP', 'MEALMP', 'AÑALMP'], true)) {
                    $imp->setAttribute($k, 0);
                } else {
                    // Resto (char/varchar) → cadena vacía
                    $imp->setAttribute($k, '');
                }
            }
        }

        // 5. Guardar cambios
        $imp->save();

        return response()->json([
            'ok' => true,
            'id' => $imp->CLAVIM,
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $imp = Importador::find($id);

        if (! $imp) {
            return response()->json([
                'message' => 'Importador no encontrado.',
            ], 404);
        }

        $imp->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Importador eliminado correctamente.'
        ]);
    }

}
