<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
class BulletinBoardController extends Controller
{
    protected $imageManager;

    public function __construct()
    {
        // Inicializar el ImageManager
        $this->imageManager =  new ImageManager(Driver::class);
    }

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
                'titulo'            => 'required|string|max:255',
                'imagen'            => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'fecha_publicacion' => 'required|date',
            ]);

            Log::info('Validación completada. Datos validados:', $validated);

            // Procesar y almacenar la imagen usando ImageManager
            if ($request->hasFile('imagen')) {
                Log::info('Imagen recibida. Procesando el almacenamiento...');

                // Obtener el archivo subido
                $file = $request->file('imagen');

                // Crear la imagen usando ImageManager
                $image = $this->imageManager->read($file->getRealPath());

                // Redimensionar la imagen
                $image->resize(1024, 768, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Generar un nombre único para el archivo
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                // Guardar la imagen redimensionada en el disco
                $filePath = 'images/' . $filename;
                Storage::disk('public')->put($filePath, (string) $image->encode());

                // Agregar la URL completa de la imagen a los datos validados
                $validated['imagen'] = 'https://backend-production-5e0d.up.railway.app/storage/' . $filePath;

                Log::info('Imagen almacenada en:', ['path' => $validated['imagen']]);
            } else {
                Log::warning('No se encontró el archivo de imagen en la solicitud.');
                return response()->json(['error' => 'No se encontró el archivo de imagen.'], 400);
            }

            // Crear el anuncio con los datos validados
            $anuncio = new BulletinBoard();
            $anuncio->titulo = $validated['titulo'];
            $anuncio->imagen = $validated['imagen'];
            $anuncio->fecha_publicacion = $validated['fecha_publicacion'];
            $anuncio->save();

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
                'titulo'            => 'sometimes|string|max:255',
                'imagen'            => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'fecha_publicacion' => 'sometimes|date',
            ]);

            // Procesar y almacenar la nueva imagen si se proporciona
            if ($request->hasFile('imagen')) {
                // Eliminar la imagen anterior si existe
                if ($anuncio->imagen && Storage::disk('public')->exists($anuncio->imagen)) {
                    Storage::disk('public')->delete($anuncio->imagen);
                }

                // Obtener el archivo subido
                $file = $request->file('imagen');

                // Crear la imagen usando ImageManager
                $image = $this->imageManager->read($file->getRealPath());

                // Redimensionar la imagen
                $image->resize(1024, 768, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Generar un nombre único para el archivo
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                // Guardar la imagen redimensionada en el disco
                $filePath = 'images/' . $filename;
                Storage::disk('public')->put($filePath, (string) $image->encode());

                // Agregar la URL completa de la imagen a los datos validados
                $validated['imagen'] = 'https://backend-production-5e0d.up.railway.app/storage/' . $filePath;
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
            if ($anuncio->imagen && Storage::disk('public')->exists($anuncio->imagen)) {
                Storage::disk('public')->delete($anuncio->imagen);
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
