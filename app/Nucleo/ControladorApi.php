<?php
declare(strict_types=1);
namespace App\Nucleo;
class ControladorApi
{
    protected function responder($datos, int $codigo = 200): void { http_response_code($codigo); header('Content-Type: application/json; charset=UTF-8'); echo json_encode($datos, JSON_UNESCAPED_UNICODE); exit; }
    protected function error(string $mensaje, int $codigo = 400): void { $this->responder(['error'=>$mensaje], $codigo); }
    protected function exito($datos = null, string $mensaje = 'OK'): void { $resp = ['mensaje'=>$mensaje]; if ($datos!==null) $resp['datos']=$datos; $this->responder($resp); }
    protected function obtenerJson(): array { $json = file_get_contents('php://input'); return json_decode($json, true) ?? []; }
    protected function verificarCampos(array $datos, array $campos): ?string { foreach ($campos as $c) if (!isset($datos[$c])||empty($datos[$c])) return "El campo '$c' es obligatorio."; return null; }
}
