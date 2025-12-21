<?php

namespace App\Http\Controllers;

use App\Models\Importador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ImportadorDocumento;

class ImportadorDocumentosController extends Controller
{
    public function index($id)
    {
        $docs = ImportadorDocumento::where('importador_clavim', (int) $id)
            ->orderByDesc('created_at')
            ->get(['id', 'nombre_original', 'mime', 'tamano', 'created_at']);

        return response()->json($docs);
    }

    public function store(Request $request, $clavim)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'], // 10MB
        ]);

        $file = $request->file('file');

        $path = "importadores/{$clavim}/" . Str::uuid() . "." . $file->getClientOriginalExtension();

        Storage::disk('s3')->put($path, file_get_contents($file), [
            'ContentType' => $file->getMimeType(),
            // Importante: privado
            'ACL' => 'private',
        ]);

        $doc = ImportadorDocumento::create([
            'importador_clavim' => (int) $clavim,
            'nombre_original' => $file->getClientOriginalName(),
            'ruta' => $path,
            'mime' => $file->getMimeType(),
            'tamano' => $file->getSize(),
        ]);

        return response()->json(['ok' => true, 'documento' => $doc], 201);
    }

    private function signedUrl(ImportadorDocumento $doc, string $disposition)
    {
        return Storage::disk('s3')->temporaryUrl(
            $doc->ruta,
            now()->addMinutes(5),
            [
                'ResponseContentDisposition' => $disposition.'; filename="'.$doc->nombre_original.'"',
                'ResponseContentType' => $doc->mime,
            ]
        );
    }

    public function view($id)
    {
        $doc = ImportadorDocumento::findOrFail($id);
        return response()->json(['url' => $this->signedUrl($doc, 'inline')]);
    }

    public function download($id)
    {
        $doc = ImportadorDocumento::findOrFail($id);
        return response()->json(['url' => $this->signedUrl($doc, 'attachment')]);
    }

    public function destroy($id)
    {
        $doc = ImportadorDocumento::findOrFail($id);

        Storage::disk('s3')->delete($doc->ruta);
        $doc->delete();

        return response()->json(['ok' => true]);
    }

}
