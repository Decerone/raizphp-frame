<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
class MiddlewareSeguridad extends Middleware
{
    public function manejar($peticion, callable $siguiente)
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'");
        return $siguiente($peticion);
    }
}
