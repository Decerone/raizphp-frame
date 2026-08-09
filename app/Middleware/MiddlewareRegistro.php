<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
use App\Nucleo\Aplicacion;
class MiddlewareRegistro extends Middleware
{
    public function manejar($peticion, callable $siguiente)
    {
        $metodo = $_SERVER['REQUEST_METHOD']??'GET'; $uri = $_SERVER['REQUEST_URI']??'/'; $ip = $_SERVER['REMOTE_ADDR']??'127.0.0.1';
        $mensaje = '['.date('Y-m-d H:i:s')."] $ip - $metodo $uri\n";
        $rutaLogs = Aplicacion::obtenerInstancia()->obtenerDirectorioRaiz().'/almacenamiento/logs/acceso-'.date('Y-m-d').'.log';
        file_put_contents($rutaLogs, $mensaje, FILE_APPEND);
        return $siguiente($peticion);
    }
}
