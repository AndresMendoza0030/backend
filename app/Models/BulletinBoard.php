<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulletinBoard extends Model
{
    use HasFactory;

    protected $table = 'bulletin_board';

    protected $fillable = [
        'titulo',
        'imagen',
        'fecha_publicacion',
    ];
}
