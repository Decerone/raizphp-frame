<?php
declare(strict_types=1);

use App\Nucleo\HelperCsrf;

class HelperCsrfTest
{
    private function iniciarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
    }

    public function testGenerarToken(): void
    {
        $this->iniciarSesion();
        $token = HelperCsrf::generarToken();
        Ayuda::verdadero(strlen($token) === 64, 'Token debe tener 64 caracteres hex');
    }

    public function testValidarTokenCorrecto(): void
    {
        $this->iniciarSesion();
        $token = HelperCsrf::generarToken();
        Ayuda::verdadero(HelperCsrf::validarToken($token));
    }

    public function testValidarTokenIncorrecto(): void
    {
        $this->iniciarSesion();
        HelperCsrf::generarToken();
        Ayuda::falso(HelperCsrf::validarToken('token-falso'));
    }

    public function testValidarTokenVacio(): void
    {
        $this->iniciarSesion();
        HelperCsrf::generarToken();
        Ayuda::falso(HelperCsrf::validarToken(''));
        Ayuda::falso(HelperCsrf::validarToken(null));
    }

    public function testCampoOculto(): void
    {
        $this->iniciarSesion();
        $campo = HelperCsrf::campoOculto();
        Ayuda::contiene('name="_token"', $campo);
        Ayuda::contiene('type="hidden"', $campo);
    }
}
