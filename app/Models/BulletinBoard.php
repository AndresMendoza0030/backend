<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BulletinBoard extends Model
{
    use HasFactory;

    protected $table = 'bulletin_board';

    protected $fillable = [
        'titulo',
        'imagen_path',
        'fecha_publicacion',
    ];

    // Accessor para la imagen URL completa
    public function getImagenUrlAttribute()
    {
        return $this->imagen_path ? Storage::url($this->imagen_path) : null;
    }
}
