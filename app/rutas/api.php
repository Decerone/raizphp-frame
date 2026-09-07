<?php
declare(strict_types=1);
use App\Nucleo\Enrutador;
use App\Middleware\MiddlewareApiAuth;
return function (Enrutador $enrutador): void {
    $enrutador->agregarRuta("POST","/api/login","AuthControlador@apiLogin");
    $enrutador->agregarRuta("GET","/api/usuarios","Api\\UsuarioApiControlador@lista");
    $enrutador->agregarRuta("GET","/api/usuarios/mostrar","Api\\UsuarioApiControlador@mostrar");
    $enrutador->agregarRuta("POST","/api/usuarios/crear","Api\\UsuarioApiControlador@crear");
    $rutas=[["GET","/api/usuarios"],["GET","/api/usuarios/mostrar"],["POST","/api/usuarios/crear"]];
    foreach($rutas as [$m,$r]) $enrutador->agregarMiddlewareRuta($m,$r,new MiddlewareApiAuth());
};
