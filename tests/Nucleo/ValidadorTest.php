<?php
declare(strict_types=1);

use App\Nucleo\Validador;

class ValidadorTest
{
    public function testEmailValido(): void
    {
        $v = new Validador(['email' => 'test@example.com']);
        $v->reglas(['email' => ['requerido', 'email']]);
        Ayuda::verdadero($v->validar());
    }

    public function testEmailInvalido(): void
    {
        $v = new Validador(['email' => 'no-es-email']);
        $v->reglas(['email' => ['requerido', 'email']]);
        Ayuda::falso($v->validar());
    }

    public function testRequeridoFalla(): void
    {
        $v = new Validador(['nombre' => '']);
        $v->reglas(['nombre' => ['requerido']]);
        Ayuda::falso($v->validar());
    }

    public function testMinimoNumerico(): void
    {
        $v = new Validador(['edad' => '9']);
        $v->reglas(['edad' => ['requerido', 'numerico', 'min:1', 'max:120']]);
        Ayuda::verdadero($v->validar(), 'Edad 9 debería pasar min:1');
    }

    public function testMaximoNumerico(): void
    {
        $v = new Validador(['edad' => '200']);
        $v->reglas(['edad' => ['requerido', 'numerico', 'min:1', 'max:120']]);
        Ayuda::falso($v->validar(), 'Edad 200 debería fallar max:120');
    }

    public function testMinimoString(): void
    {
        $v = new Validador(['nombre' => 'A']);
        $v->reglas(['nombre' => ['requerido', 'min:2']]);
        Ayuda::falso($v->validar());
    }
}
