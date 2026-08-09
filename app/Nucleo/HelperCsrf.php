<?php
declare(strict_types=1);
namespace App\Nucleo;
class HelperCsrf
{
    public static function generarToken(): string { if (!isset($_SESSION)) session_start(); $token = bin2hex(random_bytes(32)); $_SESSION['_token_csrf'] = $token; return $token; }
    public static function obtenerToken(): string { if (!isset($_SESSION)) session_start(); if (empty($_SESSION['_token_csrf'])) return self::generarToken(); return $_SESSION['_token_csrf']; }
    public static function validarToken(?string $token): bool { if (!isset($_SESSION)) session_start(); if (empty($token)||empty($_SESSION['_token_csrf'])) return false; return hash_equals($_SESSION['_token_csrf'], $token); }
    public static function campoOculto(): string { return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::obtenerToken()) . '">'; }
    public static function destruirYRegenerar(): void { if (!isset($_SESSION)) session_start(); unset($_SESSION['_token_csrf']); self::generarToken(); }
}
