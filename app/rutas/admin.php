<?php
declare(strict_types=1);
use App\Nucleo\Enrutador;
use App\Middleware\MiddlewareRol;
return function (Enrutador $enrutador): void {
    $enrutador->agregarRuta("GET","/admin","AdminControlador@index");
    $enrutador->agregarRuta("GET","/admin/cache","AdminControlador@cache");
    $enrutador->agregarRuta("POST","/admin/cache/limpiar","AdminControlador@limpiarCache");
    $enrutador->agregarRuta("GET","/admin/usuarios","Admin\\UsuarioAdminControlador@lista");
    $enrutador->agregarRuta("GET","/admin/usuarios/editar","Admin\\UsuarioAdminControlador@editar");
    $enrutador->agregarRuta("POST","/admin/usuarios/actualizar","Admin\\UsuarioAdminControlador@actualizar");
    $enrutador->agregarRuta("POST","/admin/usuarios/eliminar","Admin\\UsuarioAdminControlador@eliminar");
    $rutas=[["GET","/admin"],["GET","/admin/cache"],["POST","/admin/cache/limpiar"],["GET","/admin/usuarios"],["GET","/admin/usuarios/editar"],["POST","/admin/usuarios/actualizar"],["POST","/admin/usuarios/eliminar"]];
    foreach($rutas as [$m,$r]) $enrutador->agregarMiddlewareRuta($m,$r,new MiddlewareRol("admin"));
};
