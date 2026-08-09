<?php
declare(strict_types=1);
$directorioRaiz = dirname(__DIR__);
$configApp = require $directorioRaiz . '/config/aplicacion.php';
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($configApp['seguridad']['forzar_https'])) ini_set('session.cookie_secure', '1');
require_once $directorioRaiz . '/app/Nucleo/ManejadorErrores.php';
$manejador = new App\Nucleo\ManejadorErrores($directorioRaiz, $configApp['entorno']);
$manejador->registrar();
require_once $directorioRaiz . '/app/Nucleo/Autocargador.php';
App\Nucleo\Autocargador::registrar();
require_once $directorioRaiz . '/app/Nucleo/Aplicacion.php';
use App\Nucleo\Aplicacion;
$app = new Aplicacion($directorioRaiz);
$app->ejecutar();
