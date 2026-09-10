<?php
declare(strict_types=1);

class Ayuda
{
    public static function reiniciar(): void {}

    public static function afirmar(bool $condicion, string $mensaje = ''): void
    {
        if (!$condicion) throw new \RuntimeException($mensaje ?: 'Afirmación fallida');
    }

    public static function igual(mixed $esperado, mixed $actual, string $mensaje = ''): void
    {
        if ($esperado !== $actual) {
            throw new \RuntimeException($mensaje ?: "Esperado: " . var_export($esperado, true) . " | Actual: " . var_export($actual, true));
        }
    }

    public static function verdadero(bool $condicion, string $mensaje = ''): void
    {
        self::afirmar($condicion, $mensaje ?: 'Se esperaba true');
    }

    public static function falso(bool $condicion, string $mensaje = ''): void
    {
        self::afirmar(!$condicion, $mensaje ?: 'Se esperaba false');
    }

    public static function vacio(array $array, string $mensaje = ''): void
    {
        self::afirmar(empty($array), $mensaje ?: 'Se esperaba vacío');
    }

    public static function noVacio(array $array, string $mensaje = ''): void
    {
        self::afirmar(!empty($array), $mensaje ?: 'Se esperaba no vacío');
    }

    public static function lanzaExcepcion(callable $callback, string $tipoEsperado = ''): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            if ($tipoEsperado && !($e instanceof $tipoEsperado)) {
                throw new \RuntimeException("Se esperaba $tipoEsperado, se obtuvo " . get_class($e));
            }
            return;
        }
        throw new \RuntimeException("Se esperaba una excepción" . ($tipoEsperado ? " de tipo $tipoEsperado" : ''));
    }

    public static function contiene(string $aguja, string $pajar, string $mensaje = ''): void
    {
        if (strpos($pajar, $aguja) === false) {
            throw new \RuntimeException($mensaje ?: "'$aguja' no está en '$pajar'");
        }
    }
}
