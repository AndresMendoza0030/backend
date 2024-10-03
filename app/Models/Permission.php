<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name'
    ];

    // Accessor para obtener el nombre legible del recurso
    public function getReadableNameAttribute()
    {
        // Separamos la cadena en "acción_nombre_del_recurso"
        $parts = explode('_', $this->name, 2);

        $action = ucfirst($parts[0]); // Convertimos la acción a "Add", "Modify", etc.
        $resource = ucfirst(str_replace('_', ' ', $parts[1])); // Convertimos el recurso a "New User", "Total Users", etc.

        return "{$action} {$resource}";
    }
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
