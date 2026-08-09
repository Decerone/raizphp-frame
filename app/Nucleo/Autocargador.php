<?php
declare(strict_types=1);
namespace App\Nucleo;
class Autocargador
{
    public static function registrar(): void
    {
        spl_autoload_register(function (string $clase) {
            $prefijo = 'App\\';
            $directorioBase = dirname(__DIR__);
            if (strncmp($prefijo, $clase, strlen($prefijo)) !== 0) return;
            $claseRelativa = substr($clase, strlen($prefijo));
            $rutaArchivo = $directorioBase . '/' . str_replace('\\', '/', $claseRelativa) . '.php';
            if (file_exists($rutaArchivo)) { require $rutaArchivo; }
        });
    }
}
