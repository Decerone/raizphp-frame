<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
use App\Modelos\Usuario;
class MiddlewareApiAuth extends Middleware
{
    public function manejar($peticion, callable $siguiente)
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = '';
        if (preg_match('/Bearer\s+(.*)$/i', $header, $m)) $token = $m[1];
        if (!$token) { http_response_code(401); header('Content-Type: application/json; charset=UTF-8'); echo json_encode(['error'=>'Token de autenticación requerido.']); exit; }
        $usuario = Usuario::buscarPorToken($token);
        if (!$usuario) { http_response_code(401); header('Content-Type: application/json; charset=UTF-8'); echo json_encode(['error'=>'Token inválido o expirado.']); exit; }
        $_REQUEST['usuario_api'] = $usuario->aArray();
        return $siguiente($peticion);
    }
}
