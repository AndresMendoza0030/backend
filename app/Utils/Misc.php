<?php

namespace App\Utils;

class Misc
{
    // Método para transformar la cadena legible al nuevo patrón
    static function transformToPattern($readableName)
    {
        // Separar la acción del nombre del recurso
        $parts = explode(' ', $readableName, 2); // "Add New User" => ["Add", "New User"]

        $action = strtolower($parts[0]); // "add"
        $resource = str_replace(' ', '_', strtolower($parts[1])); // "new_user"

        return "{$action}_{$resource}";
    }
}