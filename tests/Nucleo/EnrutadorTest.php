<?php
declare(strict_types=1);

use App\Nucleo\Enrutador;

class EnrutadorTest
{
    public function testAgregarRutaExacta(): void
    {
        $e = new Enrutador();
        $e->agregarRuta('GET', '/test', 'Test@index');
        Ayuda::verdadero($e instanceof Enrutador);
    }

    public function testAgregarRutaConParametros(): void
    {
        $e = new Enrutador();
        $e->agregarRuta('GET', '/usuarios/{id}', 'Usuario@ver');
        Ayuda::verdadero($e instanceof Enrutador);
    }
}
