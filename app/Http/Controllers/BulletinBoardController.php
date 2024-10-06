<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

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

            // Revisar si la carpeta de almacenamiento existe, si no, crearla
            $folderPath = storage_path('app/images');
            if (!File::exists($folderPath)) {
                Log::warning('La carpeta de almacenamiento no existe. Intentando crearla...');
                if (!File::makeDirectory($folderPath, 0755, true)) {
                    Log::error('No se pudo crear la carpeta de almacenamiento. Verifica permisos.');
                    return response()->json(['error' => 'No se pudo crear la carpeta de almacenamiento.'], 500);
                }
                Log::info('Carpeta de almacenamiento creada correctamente.');
            }

            // Procesar y almacenar la imagen usando Storage::disk('local')->put()
            if ($request->hasFile('imagen')) {
                Log::info('Imagen recibida. Procesando el almacenamiento...');

                // Obtener el archivo subido
                $file = $request->file('imagen');

                // Generar un nombre único para el archivo
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                // Obtener el contenido del archivo
                $fileContents = file_get_contents($file->getRealPath());

                // Verificar si tenemos permisos de escritura en la carpeta de almacenamiento
                if (!is_writable($folderPath)) {
                    Log::error('La carpeta de almacenamiento no es escribible. Verifica permisos.');
                    return response()->json(['error' => 'La carpeta de almacenamiento no tiene permisos de escritura.'], 500);
                }

                // Almacenar el archivo usando Storage::disk('local')->put()
                Storage::disk('local')->put('images/' . $filename, $fileContents);

                // Agregar el nombre del archivo a los datos validados
                $validated['imagen'] = 'images/' . $filename;

                Log::info('Imagen almacenada en:', ['path' => 'images/' . $filename]);
            } else {
                Log::warning('No se encontró el archivo de imagen en la solicitud.');
                return response()->json(['error' => 'No se encontró el archivo de imagen.'], 400);
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
        try {
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
                if ($anuncio->imagen && Storage::disk('local')->exists($anuncio->imagen)) {
                    Storage::delete($anuncio->imagen);
                }

                // Obtener el archivo subido
                $file = $request->file('imagen');
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $fileContents = file_get_contents($file->getRealPath());

                // Almacenar la nueva imagen
                Storage::disk('local')->put('images/' . $filename, $fileContents);
                $validated['imagen'] = 'images/' . $filename;
            }

            // Actualizar el anuncio con los datos validados
            $anuncio->update($validated);
            Log::info('Anuncio actualizado exitosamente:', ['anuncio' => $anuncio]);

            return response()->json($anuncio, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Error de validación.', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar el anuncio: ' . $e->getMessage());
            Log::error('Detalles del stack trace:', ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error al actualizar el anuncio.'], 500);
        }
    }

    // Eliminar un anuncio
    public function destroy($id)
    {
        try {
            $anuncio = BulletinBoard::findOrFail($id);

            // Eliminar la imagen asociada si existe
            if ($anuncio->imagen && Storage::disk('local')->exists($anuncio->imagen)) {
                Storage::delete($anuncio->imagen);
            }

            $anuncio->delete();
            Log::info('Anuncio eliminado exitosamente.', ['id' => $id]);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error al eliminar el anuncio: ' . $e->getMessage());
            Log::error('Detalles del stack trace:', ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error al eliminar el anuncio.'], 500);
        }
    }
}