<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

            // Procesar y almacenar la imagen usando Storage::disk('local')->put()
            if ($request->hasFile('imagen')) {
                Log::info('Imagen recibida. Procesando el almacenamiento...');

                // Obtener el archivo subido
                $file = $request->file('imagen');

                // Generar un nombre único para el archivo
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                // Obtener el contenido del archivo
                $fileContents = file_get_contents($file->getRealPath());

                // Almacenar el archivo usando Storage::disk('local')->put()
                Storage::disk('local')->put('images/' . $filename, $fileContents);

                // Agregar el nombre del archivo a los datos validados
                $validated['imagen'] = 'images/' . $filename;

                Log::info('Imagen almacenada en:', ['path' => 'images/' . $filename]);
            } else {
                Log::warning('No se encontró el archivo de imagen en la solicitud.');
            }

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

            // Eliminar la imagen anterior si existe
            if ($anuncio->imagen) {
                Storage::delete('public/images/' . $anuncio->imagen);
            }

            // Obtener el nombre del archivo con la extensión
            $filenameWithExt = $request->file('imagen')->getClientOriginalName();

            // Obtener solo el nombre del archivo
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

            // Obtener solo la extensión
            $extension = $request->file('imagen')->getClientOriginalExtension();

            // Nombre del archivo para almacenar
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            // Subir la imagen
            $path = $request->file('imagen')->storeAs('public/images', $fileNameToStore);

            // Agregar el nombre del archivo a los datos validados
            $validated['imagen'] = $fileNameToStore;
        }

        // Actualizar el anuncio con los datos validados
        $anuncio->update($validated);

        return response()->json($anuncio, 200);
    }

    // Eliminar un anuncio
    public function destroy($id)
    {
        $anuncio = BulletinBoard::findOrFail($id);

        // Eliminar la imagen asociada si existe
        if ($anuncio->imagen) {
            Storage::delete('public/images/' . $anuncio->imagen);
        }

        $anuncio->delete();

        return response()->json(null, 204);
    }
}
