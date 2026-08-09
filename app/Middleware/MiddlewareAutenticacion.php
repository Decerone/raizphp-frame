<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
use App\Nucleo\Autenticacion;
class MiddlewareAutenticacion extends Middleware
{
    public function manejar($peticion, callable $siguiente)
    {
        $rutasPublicas = ['/login','/registro','/logout','/recuperar','/restablecer'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
        if ($base!=='/'&&$base!=='\\'&&strpos($uri,$base)===0) $uri = substr($uri, strlen($base));
        $uri = '/' . ltrim($uri, '/');
        if (strpos($uri, '/api/')===0) return $siguiente($peticion);
        if (in_array($uri, $rutasPublicas)) return $siguiente($peticion);
        if (!Autenticacion::estaAutenticado()) { $urlBase = $this->obtenerUrlBase(); header('Location: '.$urlBase.'/login'); exit; }
        return $siguiente($peticion);
    }
    private function obtenerUrlBase(): string { $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); return ($s==='/'||$s==='\\')?'':$s; }
}
