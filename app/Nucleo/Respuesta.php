<?php
declare(strict_types=1);
namespace App\Nucleo;

class Respuesta
{
    private int $codigo = 200;
    private array $headers = [];
    private string $cuerpo = '';
    
    public function __construct(string $cuerpo = '', int $codigo = 200, array $headers = [])
    {
        $this->cuerpo = $cuerpo;
        $this->codigo = $codigo;
        $this->headers = $headers;
    }
    
    public function codigo(int $codigo): self { $this->codigo = $codigo; return $this; }
    public function header(string $nombre, string $valor): self { $this->headers[$nombre] = $valor; return $this; }
    public function cuerpo(string $cuerpo): self { $this->cuerpo = $cuerpo; return $this; }
    public function obtenerCodigo(): int { return $this->codigo; }
    public function obtenerCuerpo(): string { return $this->cuerpo; }
    public function obtenerHeaders(): array { return $this->headers; }
    
    public function enviar(): void
    {
        http_response_code($this->codigo);
        foreach ($this->headers as $nombre => $valor) {
            header("$nombre: $valor");
        }
        echo $this->cuerpo;
    }
    
    public static function json($datos, int $codigo = 200): self
    {
        return new self(
            json_encode($datos, JSON_UNESCAPED_UNICODE),
            $codigo,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
    
    public static function html(string $html, int $codigo = 200): self
    {
        return new self($html, $codigo, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
    
    public static function redirigir(string $url, int $codigo = 302): self
    {
        return new self('', $codigo, ['Location' => $url]);
    }
    
    public static function error(string $mensaje, int $codigo = 400): self
    {
        return self::json(['error' => $mensaje], $codigo);
    }
    
    public static function exito($datos = null, string $mensaje = 'OK'): self
    {
        $resp = ['mensaje' => $mensaje];
        if ($datos !== null) $resp['datos'] = $datos;
        return self::json($resp, 200);
    }
}
