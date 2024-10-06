<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BulletinBoardController extends Controller
{
    // Obtener todos los anuncios
    public function index()
    {
        return BulletinBoard::all();
    }

    // Crear un nuevo anuncio
    public function store(Request $request)
    {
        try {
            Log::info('Iniciando el almacenamiento de un nuevo anuncio...');
            Log::info('Datos de la solicitud:', $request->all());
    
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'fecha_publicacion' => 'required|date',
            ]);
    
            Log::info('Validación completada. Datos validados:', $validated);
    
            if ($request->hasFile('imagen')) {
                Log::info('Imagen recibida. Procesando el almacenamiento...');
                // Almacenar la imagen en la carpeta 'public/images'
                $path = $request->file('imagen')->storeAs('images', uniqid() . '.' . $request->file('imagen')->extension(), 'public');

                $validated['imagen_path'] = $path;
                Log::info('Imagen almacenada en:', $path);
            } else {
                Log::warning('No se encontró el archivo de imagen en la solicitud.');
            }
    
            $anuncio = BulletinBoard::create($validated);
            Log::info('Anuncio creado exitosamente:', $anuncio);
    
            return response()->json($anuncio, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', $e->errors());
            return response()->json(['error' => 'Error de validación.', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear el anuncio: ' . $e->getMessage());
            Log::error('Detalles del stack trace:', ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error al crear el anuncio.'], 500);
        }
    }

    // Obtener un anuncio específico
    public function show($id)
    {
        return BulletinBoard::findOrFail($id);
    }

    // Actualizar un anuncio existente
    public function update(Request $request, $id)
    {
        $anuncio = BulletinBoard::findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'string|max:255',
            'imagen' => 'string',
            'fecha_publicacion' => 'date',
        ]);

        $anuncio->update($validated);

        return response()->json($anuncio, 200);
    }

    // Eliminar un anuncio
    public function destroy($id)
    {
        $anuncio = BulletinBoard::findOrFail($id);
        $anuncio->delete();

        return response()->json(null, 204);
    }
}
