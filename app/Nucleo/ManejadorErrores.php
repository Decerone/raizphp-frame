<?php
declare(strict_types=1);
namespace App\Nucleo;
use Throwable;
class ManejadorErrores
{
    private string $entorno;
    private string $directorioRaiz;
    
    public function __construct(string $directorioRaiz, string $entorno = 'produccion')
    {
        $this->directorioRaiz = rtrim($directorioRaiz, '/');
        $this->entorno = $entorno;
    }
    
    public function registrar(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0'); // Nunca mostrar errores PHP crudos
        set_exception_handler([$this, 'manejarExcepcion']);
        set_error_handler([$this, 'manejarError']);
        register_shutdown_function([$this, 'manejarErrorFatal']);
    }
    
    public function manejarExcepcion(Throwable $excepcion): void
    {
        $this->registrarError($excepcion);
        http_response_code(500);
        
        if ($this->entorno === 'desarrollo') {
            $this->mostrarErrorDesarrollo($excepcion);
        } else {
            $this->mostrarPaginaError(500);
        }
    }
    
    public function manejarError(int $codigo, string $mensaje, string $archivo, int $linea): bool
    {
        throw new \ErrorException($mensaje, $codigo, 1, $archivo, $linea);
    }
    
    public function manejarErrorFatal(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $excepcion = new \ErrorException($error['message'], $error['type'], 1, $error['file'], $error['line']);
            $this->registrarError($excepcion);
            http_response_code(500);
            if ($this->entorno === 'desarrollo') {
                $this->mostrarErrorDesarrollo($excepcion);
            } else {
                $this->mostrarPaginaError(500);
            }
        }
    }
    
    private function mostrarErrorDesarrollo(Throwable $excepcion): void
    {
        $clase = get_class($excepcion);
        $mensaje = $excepcion->getMessage();
        $archivo = $excepcion->getFile();
        $linea = $excepcion->getLine();
        $trace = $excepcion->getTraceAsString();
        
        // Resaltar la línea del error
        $lineasCodigo = '';
        if (file_exists($archivo)) {
            $codigo = file($archivo);
            $inicio = max(0, $linea - 5);
            $fin = min(count($codigo), $linea + 3);
            for ($i = $inicio; $i < $fin; $i++) {
                $num = $i + 1;
                $esError = ($num == $linea);
                $lineasCodigo .= '<div style="' . ($esError ? 'background:#fee2e2;' : '') . 'padding:2px 8px;font-family:monospace;font-size:13px;">';
                $lineasCodigo .= '<span style="color:#94a3b8;margin-right:12px;">' . str_pad((string)$num, 4, ' ', STR_PAD_LEFT) . '</span>';
                $lineasCodigo .= '<span style="color:' . ($esError ? '#dc2626;font-weight:bold;' : '#1e293b;') . '">' . htmlspecialchars(rtrim($codigo[$i])) . '</span>';
                $lineasCodigo .= '</div>';
            }
        }
        
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🐛 Error - RaízPHP</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,sans-serif;background:#1e1b4b;color:#e2e8f0;padding:2rem}
        .contenedor{max-width:1000px;margin:0 auto}
        .cabecera{background:#dc2626;padding:1.5rem;border-radius:1rem 1rem 0 0}
        .cabecera h1{font-size:1.5rem;color:white}
        .cabecera .tipo{font-size:0.85rem;opacity:0.8;margin-top:0.3rem}
        .cuerpo{background:#0f172a;padding:1.5rem;border-radius:0 0 1rem 1rem}
        .seccion{margin-bottom:1.5rem}
        .seccion h3{color:#818cf8;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.5rem}
        .seccion .contenido{background:#1e293b;padding:1rem;border-radius:0.5rem;font-family:monospace;font-size:13px;line-height:1.6;overflow-x:auto}
        .codigo-linea{display:flex}
        .codigo-num{color:#64748b;min-width:40px;text-align:right;margin-right:12px}
        .codigo-texto{color:#e2e8f0}
        .codigo-error{background:#7f1d1d;color:#fca5a5}
        .trace{color:#94a3b8;font-size:12px;line-height:1.8;white-space:pre-wrap}
        .solucion{background:#14532d;color:#86efac;padding:1rem;border-radius:0.5rem;margin-top:1.5rem}
        .solucion code{background:#166534;padding:2px 6px;border-radius:3px}
    </style>
</head>
<body>
    <div class="contenedor">
        <div class="cabecera">
            <h1>🐛 ' . htmlspecialchars($clase) . '</h1>
            <div class="tipo">Entorno: Desarrollo | RaízPHP v2.1</div>
        </div>
        <div class="cuerpo">
            <div class="seccion">
                <h3>📋 Mensaje</h3>
                <div class="contenido">' . htmlspecialchars($mensaje) . '</div>
            </div>
            <div class="seccion">
                <h3>📁 Archivo: <span style="color:#fbbf24;">' . htmlspecialchars($archivo) . '</span> (línea ' . $linea . ')</h3>
                <div class="contenido" style="padding:0;">' . $lineasCodigo . '</div>
            </div>
            <div class="seccion">
                <h3>🔍 Traza de ejecución</h3>
                <div class="contenido trace">' . htmlspecialchars($trace) . '</div>
            </div>
            <div class="solucion">
                💡 <strong>Sugerencia:</strong> Revisa la línea <code>' . $linea . '</code> en <code>' . basename($archivo) . '</code>. Verifica que las claves del array o métodos existan.
            </div>
        </div>
    </div>
</body>
</html>';
    }
    
    private function mostrarPaginaError(int $codigoHttp): void
    {
        $rutaVista = $this->directorioRaiz . '/app/Vistas/errores/' . $codigoHttp . '.php';
        if (file_exists($rutaVista)) {
            include $rutaVista;
        } else {
            echo "<h1>Error $codigoHttp</h1><p>Algo salió mal.</p>";
        }
    }
    
    private function registrarError(Throwable $excepcion): void
    {
        $carpetaLogs = $this->directorioRaiz . '/almacenamiento/logs';
        if (!is_dir($carpetaLogs)) {
            mkdir($carpetaLogs, 0755, true);
        }
        $archivoLog = $carpetaLogs . '/error-' . date('Y-m-d') . '.log';
        $mensaje = '[' . date('Y-m-d H:i:s') . '] ' . $this->formatearExcepcion($excepcion) . "\n\n";
        file_put_contents($archivoLog, $mensaje, FILE_APPEND);
    }
    
    private function formatearExcepcion(Throwable $excepcion): string
    {
        return get_class($excepcion) . ': ' . $excepcion->getMessage() . 
               ' en ' . $excepcion->getFile() . ':' . $excepcion->getLine() . 
               "\nTrace: " . $excepcion->getTraceAsString();
    }
}
