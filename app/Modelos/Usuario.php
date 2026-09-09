<?php
declare(strict_types=1);
namespace App\Modelos;
use App\Nucleo\ModeloBase;

class Usuario extends ModeloBase
{
    protected static string $tabla = 'usuarios';
    protected static string $clavePrimaria = 'id';

    public function nombreCompleto(): string
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public static function buscarPorEmail(string $email): ?self
    {
        return static::consultar()->donde('email', '=', $email)->primero();
    }

    public static function buscarPorToken(string $token): ?self
    {
        // TODO: En tarea C7 se guardará el hash del token (sha256).
        // Por ahora el token se compara en claro.
        if (empty($token)) return null;
        return static::consultar()->donde('api_token', '=', $token)->primero();
    }

    public function verificarPassword(string $password): bool
    {
        return password_verify($password, $this->password ?? '');
    }

    public function generarToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->api_token = $token;
        $this->guardar();
        return $token;
    }
}
