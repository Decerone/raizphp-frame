<?php
declare(strict_types=1);
$directorioRaiz=dirname(__DIR__);
$configApp=require $directorioRaiz."/config/aplicacion.php";
ini_set("session.cookie_httponly","1"); ini_set("session.cookie_samesite","Lax");
if(!empty($configApp["seguridad"]["forzar_https"])) ini_set("session.cookie_secure","1");
session_start();
if (empty($_SESSION["_token_csrf"])) { $_SESSION["_token_csrf"] = bin2hex(random_bytes(32)); }
require_once $directorioRaiz."/app/Nucleo/ManejadorErrores.php";
$manejador=new App\Nucleo\ManejadorErrores($directorioRaiz,$configApp["entorno"]); $manejador->registrar();
require_once $directorioRaiz."/app/Nucleo/Autocargador.php"; App\Nucleo\Autocargador::registrar();
require_once $directorioRaiz."/app/Nucleo/UrlHelper.php";
require_once $directorioRaiz."/app/Nucleo/helpers.php";
require_once $directorioRaiz."/app/Nucleo/Aplicacion.php";
use App\Nucleo\Aplicacion,App\Nucleo\UrlHelper;
define("URL_BASE",UrlHelper::base()); define("URL_COMPLETA",UrlHelper::baseCompleta());
$app=new Aplicacion($directorioRaiz); $app->ejecutar();
