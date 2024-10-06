<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use Illuminate\Http\Request;

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
    $validated = $request->validate([
        'titulo' => 'required|string|max:255',
        'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'fecha_publicacion' => 'required|date',
    ]);

    if ($request->hasFile('imagen')) {
        // Almacenar la imagen en la carpeta 'public/bulletin_images'
        $path = $request->file('imagen')->store('bulletin_images', 'public');
        $validated['imagen_path'] = $path;
    }

    $anuncio = BulletinBoard::create($validated);

    return response()->json($anuncio, 201);
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
