<?php
declare(strict_types=1);
namespace App\Nucleo;
class ControladorBase
{
    protected function renderizar(string $vista, array $datos = [], ?string $plantilla = 'plantilla', bool $usarCache = true): void
    {
        $renderizador = new Renderizador(Aplicacion::obtenerInstancia()->obtenerDirectorioRaiz() . '/app/Vistas');
        if (!isset($datos['urlBase'])) { 
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php'; 
            $urlBase = rtrim(dirname(dirname(dirname($scriptName))), '/'); 
            if ($urlBase === '/' || $urlBase === '\\') $urlBase = ''; 
            $datos['urlBase'] = $urlBase; 
        }
        $renderizador->mostrar($vista, $datos, $plantilla, $usarCache);
    }
}
