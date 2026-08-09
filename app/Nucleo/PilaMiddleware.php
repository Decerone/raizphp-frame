<?php
declare(strict_types=1);
namespace App\Nucleo;
class PilaMiddleware { private array $middlewares = []; public function agregar(Middleware $m): void { $this->middlewares[] = $m; } public function ejecutar($peticion, callable $destino) { $pila = $this->middlewares; $siguiente = function($p) use (&$pila, $destino, &$siguiente) { if (empty($pila)) return $destino($p); $m = array_shift($pila); return $m->manejar($p, $siguiente); }; return $siguiente($peticion); } }
