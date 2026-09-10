<?php
declare(strict_types=1);
namespace App\Modelos;
use App\Nucleo\ModeloBase;

class Usuario extends ModeloBase
{
    protected static string $tabla = 'usuarios';
    protected static string $clavePrimaria = 'id';
    
    protected static array $rellenables = [
        'nombre',
        'apellido',
        'email',
        'password',
        'edad'
    ];

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
        if (empty($token)) return null;
        // C7: Buscar por hash SHA-256 (nunca en claro)
        $hash = hash('sha256', $token);
        return static::consultar()->donde('api_token', '=', $hash)->primero();
    }

    public function verificarPassword(string $password): bool
    {
        return password_verify($password, $this->password ?? '');
    }

    public function generarToken(): string
    {
        $token = bin2hex(random_bytes(32));
        // C7: Guardar SOLO el hash, nunca el token en claro
        $this->api_token = hash('sha256', $token);
        $this->guardar();
        return $token; // Se muestra UNA vez
    }
}
