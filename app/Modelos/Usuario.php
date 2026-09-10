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
    
    // D3: Campos que NO se exponen en aArray()
    protected static array $ocultos = ['password', 'api_token'];
    
    // D3: Conversión automática de tipos
    protected static array $casts = [
        'id' => 'int',
        'edad' => 'int'
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
        $hash = hash('sha256', $token);
        return static::consultar()->donde('api_token', '=', $hash)->primero();
    }

    public function verificarPassword(string $password): bool
    {
        // Usamos aArrayCompleto porque password está en $ocultos
        $hash = $this->atributos['password'] ?? '';
        return password_verify($password, $hash);
    }

    public function generarToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->api_token = hash('sha256', $token);
        $this->guardar();
        return $token;
    }
}
