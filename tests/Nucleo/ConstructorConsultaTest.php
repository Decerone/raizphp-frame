<?php
declare(strict_types=1);

use App\Nucleo\ConstructorConsulta;

class ConstructorConsultaTest
{
    public function testTablaValida(): void
    {
        $cc = new ConstructorConsulta('Test', 'usuarios');
        Ayuda::verdadero($cc instanceof ConstructorConsulta);
    }

    public function testTablaInvalidaLanzaExcepcion(): void
    {
        Ayuda::lanzaExcepcion(function() {
            new ConstructorConsulta('Test', 'usuarios; DROP TABLE usuarios');
        }, \InvalidArgumentException::class);
    }

    public function testColumnaInvalidaLanzaExcepcion(): void
    {
        Ayuda::lanzaExcepcion(function() {
            $cc = new ConstructorConsulta('Test', 'usuarios');
            $cc->donde('nombre; DROP TABLE', '=', 'x');
        }, \InvalidArgumentException::class);
    }

    public function testOperadorInvalidoLanzaExcepcion(): void
    {
        Ayuda::lanzaExcepcion(function() {
            $cc = new ConstructorConsulta('Test', 'usuarios');
            $cc->donde('nombre', 'OR 1=1', 'x');
        }, \InvalidArgumentException::class);
    }

    public function testActualizarSinWhereLanzaExcepcion(): void
    {
        Ayuda::lanzaExcepcion(function() {
            $cc = new ConstructorConsulta('Test', 'usuarios');
            $cc->actualizar(['nombre' => 'X']);
        }, \RuntimeException::class);
    }

    public function testEliminarSinWhereLanzaExcepcion(): void
    {
        Ayuda::lanzaExcepcion(function() {
            $cc = new ConstructorConsulta('Test', 'usuarios');
            $cc->eliminar();
        }, \RuntimeException::class);
    }

    public function testDireccionOrdenInvalidaLanzaExcepcion(): void
    {
        Ayuda::lanzaExcepcion(function() {
            $cc = new ConstructorConsulta('Test', 'usuarios');
            $cc->ordenarPor('nombre', 'RANDOM');
        }, \InvalidArgumentException::class);
    }
}
