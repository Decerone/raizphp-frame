<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
use App\Nucleo\Aplicacion;

class MiddlewareCors extends Middleware
{
    public function manejar($peticion, callable $siguiente)
    {
        $configApp = Aplicacion::obtenerInstancia()->obtenerConfiguracion('app');
        $origenesPermitidos = $configApp['cors']['origenes'] ?? [];
        
        // CORS desactivado por defecto (sin * global)
        if (empty($origenesPermitidos)) {
            return $siguiente($peticion);
        }
        
        $origen = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origen, $origenesPermitidos, true) || in_array('*', $origenesPermitidos, true)) {
            header('Access-Control-Allow-Origin: ' . ($origen ?: '*'));
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Access-Control-Max-Age: 86400');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            return;
        }
        
        return $siguiente($peticion);
    }
}
