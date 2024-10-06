<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Importar la fachada Storage

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

            // Validar los datos de la solicitud
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'fecha_publicacion' => 'required|date',
            ]);

            Log::info('Validación completada. Datos validados:', $validated);

            // Procesar y almacenar la imagen (sin manipulación)
            if ($request->hasFile('imagen')) {
                Log::info('Imagen recibida. Procesando el almacenamiento...');
                // Generar un nombre único para la imagen
                $imageName = uniqid() . '.' . $request->file('imagen')->extension();
                // Almacenar la imagen en la carpeta 'public/images'
                $path = $request->file('imagen')->storeAs('images', $imageName, 'public');

                // Agregar el path de la imagen a los datos validados
                $validated['imagen'] = $path;
                Log::info('Imagen almacenada en:', ['path' => $path]);
            } else {
                Log::warning('No se encontró el archivo de imagen en la solicitud.');
            }

            // Añadir la línea que deseas probar
            Storage::disk('local')->put('file.txt', 'Contents');

            // Crear el anuncio con los datos validados
            $anuncio = BulletinBoard::create($validated);
            Log::info('Anuncio creado exitosamente:', ['anuncio' => $anuncio]);

            return response()->json($anuncio, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', ['errors' => $e->errors()]);
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

        // Validar los datos de la solicitud
        $validated = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'imagen' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'fecha_publicacion' => 'sometimes|date',
        ]);

        // Procesar y almacenar la nueva imagen si se proporciona
        if ($request->hasFile('imagen')) {
            // Generar un nombre único para la imagen
            $imageName = uniqid() . '.' . $request->file('imagen')->extension();
            // Almacenar la imagen en la carpeta 'public/images'
            $path = $request->file('imagen')->storeAs('images', $imageName, 'public');
            // Agregar el path de la imagen a los datos validados
            $validated['imagen'] = $path;
        }

        // Actualizar el anuncio con los datos validados
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
