<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
use App\Nucleo\Peticion;
use App\Nucleo\Autenticacion;

class MiddlewareAutenticacion extends Middleware
{
    public function manejar(Peticion $peticion, callable $siguiente)
    {
        $uri = $peticion->uri();
        $base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
        if ($base !== '/' && $base !== '\\' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri, '/');
        
        // Rutas API usan Bearer, no sesión
        if (strpos($uri, '/api/') === 0) return $siguiente($peticion);
        
        // Si no está autenticado, redirigir al login
        if (!Autenticacion::estaAutenticado()) {
            $urlBase = $this->obtenerUrlBase();
            header('Location: ' . $urlBase . '/login');
            exit;
        }
        
        return $siguiente($peticion);
    }
    
    private function obtenerUrlBase(): string
    {
        $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
        return ($s === '/' || $s === '\\') ? '' : $s;
    }
}
