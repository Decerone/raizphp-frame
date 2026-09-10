<?php
declare(strict_types=1);
namespace App\Nucleo;

class Enrutador {
    private array $rutas = [];
    private array $rutasConParametros = [];
    private array $middlewaresRuta = [];
    private PilaMiddleware $pila;
    private ?Renderizador $renderizador = null;
    
    public function __construct() { $this->pila = new PilaMiddleware(); }
    
    public function setRenderizador(Renderizador $r): void { $this->renderizador = $r; }
    
    public function usarMiddleware(Middleware $m): void { $this->pila->agregar($m); }
    
    public function agregarRuta(string $met, string $ruta, string $man): void {
        // Detectar si la ruta tiene parámetros {id}
        if (strpos($ruta, '{') !== false) {
            $this->rutasConParametros[$met][] = [
                'patron' => $ruta,
                'regex' => $this->convertirARegex($ruta),
                'manejador' => $man
            ];
        } else {
            $this->rutas[$met][$ruta] = $man;
        }
    }
    
    public function agregarMiddlewareRuta(string $met, string $ruta, Middleware $m): void {
        $this->middlewaresRuta[$met][$ruta][] = $m;
    }
    
    private function convertirARegex(string $ruta): string {
        // Convertir /usuarios/{id}/editar en regex
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $ruta);
        return '#^' . $regex . '$#';
    }
    
    private function obtenerUriBase(): string {
        $s = $_SERVER["SCRIPT_NAME"] ?? "/index.php";
        $b = dirname(dirname(dirname($s)));
        if ($b === "/" || $b === "\\") $b = "";
        return $b;
    }
    
    public function despachar(string $met, string $uri): void {
        try {
            $uri = parse_url($uri, PHP_URL_PATH);
            $uri = rtrim($uri, "/") ?: "/";
            $base = $this->obtenerUriBase();
            
            if ($base !== "" && strpos($uri, $base) === 0) {
                $uri = substr($uri, strlen($base));
                $uri = "/" . ltrim($uri, "/");
            }
            if ($uri === "" || $uri === false) $uri = "/";
            
            $manejador = null;
            $parametros = [];
            
            // 1. Buscar ruta exacta
            if (isset($this->rutas[$met][$uri])) {
                $manejador = $this->rutas[$met][$uri];
            }
            // 2. Buscar ruta con parámetros
            elseif (isset($this->rutasConParametros[$met])) {
                foreach ($this->rutasConParametros[$met] as $ruta) {
                    if (preg_match($ruta['regex'], $uri, $matches)) {
                        $manejador = $ruta['manejador'];
                        // Extraer solo parámetros con nombre
                        foreach ($matches as $key => $value) {
                            if (!is_int($key)) $parametros[$key] = $value;
                        }
                        break;
                    }
                }
            }
            
            if ($manejador === null) {
                $this->mostrarError(404);
                return;
            }
            
            // Middlewares de ruta (usa el patrón original)
            if (isset($this->middlewaresRuta[$met][$uri])) {
                foreach ($this->middlewaresRuta[$met][$uri] as $m) {
                    $this->pila->agregar($m);
                }
            }
            
            [$ctrl, $acc] = explode("@", $manejador);
            $cc = "App\\Controladores\\" . $ctrl;
            
            if (!class_exists($cc)) {
                throw new \RuntimeException("Controlador $cc no encontrado.");
            }
            
            $dest = function() use ($cc, $acc, $parametros) {
                $i = new $cc();
                // Pasar parámetros al método si los hay
                if (!empty($parametros)) {
                    return call_user_func_array([$i, $acc], $parametros);
                }
                return call_user_func([$i, $acc]);
            };
            
            $this->pila->ejecutar($_SERVER, $dest);
            
        } catch (\Throwable $e) {
            error_log("Error 500: " . $e->getMessage());
            $this->mostrarError(500);
        }
    }
    
    private function mostrarError(int $codigo): void {
        http_response_code($codigo);
        if ($this->renderizador !== null) {
            try {
                $this->renderizador->mostrar("errores/$codigo", ["urlBase" => UrlHelper::base() ?: "/"], null, false);
                return;
            } catch (\RuntimeException $e) {}
        }
        echo match($codigo) {
            404 => "404 - Pagina no encontrada",
            500 => "500 - Error interno del servidor",
            default => "Error " . $codigo
        };
    }
}
