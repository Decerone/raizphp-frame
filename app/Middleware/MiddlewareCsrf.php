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
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Ignorar API y rutas de autenticación
    if (strpos($uri, '/api/') !== false || strpos($uri, '/login') !== false || strpos($uri, '/registro') !== false) {
        return $siguiente($peticion);
    }
    
    if (in_array($metodo, ['POST','PUT','DELETE'])) {
        $token = $_POST['_token'] ?? '';
        if (!HelperCsrf::validarToken($token)) {
            http_response_code(403);
            die('Token CSRF inválido.');
        }
    }
    return $siguiente($peticion);
}
}
