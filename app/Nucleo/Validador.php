<?php
declare(strict_types=1);
namespace App\Nucleo;
class Validador
{
    private array $datos; private array $reglas; private array $errores = []; private array $nombres = [];
    public function __construct(array $datos) { $this->datos = $datos; }
    public function reglas(array $reglas): self { $this->reglas = $reglas; return $this; }
    public function nombres(array $nombres): self { $this->nombres = $nombres; return $this; }
    public function validar(): bool { $this->errores = []; foreach ($this->reglas as $campo => $reglas) { $valor = $this->datos[$campo] ?? null; foreach ($reglas as $regla) { $params = []; if (str_contains($regla, ':')) { [$regla, $p] = explode(':', $regla, 2); $params = explode(',', $p); } $metodo = 'validar'.ucfirst($regla); if (method_exists($this, $metodo)) $this->$metodo($campo, $valor, $params); if (isset($this->errores[$campo])) break; } } return empty($this->errores); }
    public function obtenerErrores(): array { return $this->errores; }
    public function obtenerError(string $campo): ?string { return $this->errores[$campo] ?? null; }
    private function validarRequerido(string $c, $v, array $p): void { if ($v===null||$v==='') $this->agregarError($c,'El campo {campo} es obligatorio.'); }
    private function validarEmail(string $c, $v, array $p): void { if (!empty($v)&&!filter_var($v,FILTER_VALIDATE_EMAIL)) $this->agregarError($c,'El campo {campo} debe ser un email válido.'); }
    private function validarMin(string $c, $v, array $p): void { $min=(int)($p[0]??0); if (is_string($v)&&mb_strlen($v)<$min) $this->agregarError($c,"El campo {campo} debe tener al menos $min caracteres."); }
    private function validarMax(string $c, $v, array $p): void { $max=(int)($p[0]??0); if (is_string($v)&&mb_strlen($v)>$max) $this->agregarError($c,"El campo {campo} no puede superar $max caracteres."); }
    private function validarNumerico(string $c, $v, array $p): void { if (!empty($v)&&!is_numeric($v)) $this->agregarError($c,'El campo {campo} debe ser numérico.'); }
    private function validarUnico(string $c, $v, array $p): void { if (empty($v)) return; $tabla=$p[0]??''; $col=$p[1]??$c; try { $pdo=Aplicacion::obtenerInstancia()->obtenerConexion()->obtenerPDO(); $stmt=$pdo->prepare("SELECT COUNT(*) FROM $tabla WHERE $col = ?"); $stmt->execute([$v]); if ($stmt->fetchColumn()>0) $this->agregarError($c,'El campo {campo} ya está registrado.'); } catch (\Throwable $e) {} }
    private function validarSeguro(string $c, $v, array $p): void { if (empty($v)) return; $errs=[]; if (mb_strlen($v)<8) $errs[]='8 caracteres'; if (!preg_match('/[A-Z]/',$v)) $errs[]='mayúscula'; if (!preg_match('/[a-z]/',$v)) $errs[]='minúscula'; if (!preg_match('/[0-9]/',$v)) $errs[]='número'; if (!preg_match('/[\W_]/',$v)) $errs[]='símbolo'; if (!empty($errs)) $this->agregarError($c,'El campo {campo} debe contener: '.implode(', ',$errs).'.'); }
    private function agregarError(string $c, string $m): void { $nombre = $this->nombres[$c] ?? $c; $this->errores[$c] = str_replace('{campo}', $nombre, $m); }
}
