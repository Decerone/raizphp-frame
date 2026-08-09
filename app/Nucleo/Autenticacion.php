<?php
declare(strict_types=1);
namespace App\Nucleo;
class Autenticacion
{
    public static function iniciarSesion(array $usuario): void
    {
        if (!isset($_SESSION)) session_start();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'] ?? 'usuario';
        session_regenerate_id(true);
    }
    public static function estaAutenticado(): bool
    {
        if (!isset($_SESSION)) session_start();
        return isset($_SESSION['usuario_id']);
    }
    public static function obtenerUsuario(): ?array
    {
        if (!self::estaAutenticado()) return null;
        return ['id'=>$_SESSION['usuario_id'],'nombre'=>$_SESSION['usuario_nombre']??'','email'=>$_SESSION['usuario_email']??'','rol'=>$_SESSION['usuario_rol']??'usuario'];
    }
    public static function cerrarSesion(): void
    {
        if (!isset($_SESSION)) session_start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) { $params = session_get_cookie_params(); setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]); }
        session_destroy();
    }
    public static function hashearPassword(string $password): string { return password_hash($password, PASSWORD_BCRYPT); }
    public static function verificarPassword(string $password, string $hash): bool { return password_verify($password, $hash); }
}
