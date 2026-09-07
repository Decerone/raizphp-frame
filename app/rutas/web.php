<?php
declare(strict_types=1);
use App\Nucleo\Enrutador;
return function (Enrutador $enrutador): void {
    $enrutador->agregarRuta("GET","/","InicioControlador@index");
    $enrutador->agregarRuta("GET","/login","AuthControlador@formularioLogin");
    $enrutador->agregarRuta("POST","/login","AuthControlador@iniciarSesion");
    $enrutador->agregarRuta("GET","/registro","AuthControlador@formularioRegistro");
    $enrutador->agregarRuta("POST","/registro","AuthControlador@registrar");
    $enrutador->agregarRuta("GET","/logout","AuthControlador@cerrarSesion");
    $enrutador->agregarRuta("GET","/recuperar","RecuperacionControlador@solicitar");
    $enrutador->agregarRuta("POST","/recuperar","RecuperacionControlador@enviarEnlace");
    $enrutador->agregarRuta("GET","/restablecer","RecuperacionControlador@restablecer");
    $enrutador->agregarRuta("POST","/restablecer","RecuperacionControlador@cambiarPassword");
};
