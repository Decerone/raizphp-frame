<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
use App\Nucleo\Autenticacion;
class MiddlewareRol extends Middleware
{
    private array $roles;
    public function __construct($roles) { $this->roles = is_array($roles) ? $roles : [$roles]; }
    public function manejar($peticion, callable $siguiente)
    {
        if (!Autenticacion::estaAutenticado()) { header('Location: '.$this->obtenerUrlBase().'/login'); exit; }
        $usuario = Autenticacion::obtenerUsuario();
        if (!in_array($usuario['rol']??'usuario', $this->roles)) { http_response_code(403); die('Acceso denegado.'); }
        return $siguiente($peticion);
    }
    private function obtenerUrlBase(): string { $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); return ($s==='/'||$s==='\\')?'':$s; }
}
