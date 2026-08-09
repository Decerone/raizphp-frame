<?php
declare(strict_types=1);
namespace App\Nucleo;
use Throwable;
class ManejadorErrores
{
    private string $entorno; private string $directorioRaiz;
    public function __construct(string $directorioRaiz, string $entorno = 'produccion') { $this->directorioRaiz = rtrim($directorioRaiz, '/'); $this->entorno = $entorno; }
    public function registrar(): void { error_reporting(E_ALL); ini_set('display_errors', $this->entorno==='desarrollo'?'1':'0'); set_exception_handler([$this,'manejarExcepcion']); set_error_handler([$this,'manejarError']); register_shutdown_function([$this,'manejarErrorFatal']); }
    public function manejarExcepcion(Throwable $excepcion): void { $this->registrarError($excepcion); $this->mostrarPaginaError(500, $excepcion); }
    public function manejarError(int $codigo, string $mensaje, string $archivo, int $linea): bool { throw new \ErrorException($mensaje, $codigo, 1, $archivo, $linea); }
    public function manejarErrorFatal(): void { $error = error_get_last(); if ($error !== null && in_array($error['type'], [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR])) { $this->registrarError(new \ErrorException($error['message'],$error['type'],1,$error['file'],$error['line'])); $this->mostrarPaginaError(500); } }
    private function mostrarPaginaError(int $codigoHttp, ?Throwable $excepcion = null): void { http_response_code($codigoHttp); if ($this->entorno==='desarrollo') { echo "<h1>Error $codigoHttp</h1>"; if ($excepcion) echo '<pre>'.$this->formatearExcepcion($excepcion).'</pre>'; return; } $rutaVista = $this->directorioRaiz.'/app/Vistas/errores/'.$codigoHttp.'.php'; if (file_exists($rutaVista)) include $rutaVista; else echo "<h1>Error interno del servidor</h1>"; }
    private function registrarError(Throwable $excepcion): void { $carpetaLogs = $this->directorioRaiz.'/almacenamiento/logs'; if (!is_dir($carpetaLogs)) mkdir($carpetaLogs, 0755, true); $archivoLog = $carpetaLogs.'/error-'.date('Y-m-d').'.log'; $mensaje = '['.date('Y-m-d H:i:s').'] '.$this->formatearExcepcion($excepcion)."\n\n"; file_put_contents($archivoLog, $mensaje, FILE_APPEND); }
    private function formatearExcepcion(Throwable $excepcion): string { return get_class($excepcion).': '.$excepcion->getMessage().' en '.$excepcion->getFile().':'.$excepcion->getLine()."\nTrace: ".$excepcion->getTraceAsString(); }
}
