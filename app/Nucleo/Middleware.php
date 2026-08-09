<?php
declare(strict_types=1);
namespace App\Nucleo;
abstract class Middleware { abstract public function manejar($peticion, callable $siguiente); }
