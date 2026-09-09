<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
use App\Nucleo\Aplicacion;

class MiddlewareHttps extends Middleware
{
    public function manejar($peticion, callable $siguiente)
    {
        $configApp = Aplicacion::obtenerInstancia()->obtenerConfiguracion('app');
        $forzarHttps = $configApp['seguridad']['forzar_https'] ?? false;
        
        if (!$forzarHttps) {
            return $siguiente($peticion);
        }
        
        $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        
        // Soporte para proxy configurado
        $confiarProxy = $configApp['seguridad']['confiar_proxy'] ?? false;
        if (!$esHttps && $confiarProxy) {
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
            $esHttps = (strtolower($proto) === 'https');
        }
        
        if (!$esHttps) {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: https://' . $host . $uri, true, 301);
            exit;
        }
        
        return $siguiente($peticion);
    }
}
