<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
use App\Nucleo\HelperCsrf;

class MiddlewareCsrf extends Middleware
{
    public function manejar($peticion, callable $siguiente)
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '/';
        $uri = rtrim($uri, '/') ?: '/';
        
        // Solo protegemos mutaciones web (no API)
        if (str_starts_with($uri, '/api/')) {
            return $siguiente($peticion);
        }
        
        // Proteger TODAS las mutaciones: POST, PUT, PATCH, DELETE
        if (in_array($metodo, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $_POST['_token'] ?? '';
            if (!HelperCsrf::validarToken($token)) {
                http_response_code(403);
                die('Token CSRF inválido.');
            }
        }
        
        return $siguiente($peticion);
    }
}
