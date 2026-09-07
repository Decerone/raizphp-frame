<?php
declare(strict_types=1);
use App\Nucleo\UrlHelper;
if (!function_exists("url")) { function url(string $ruta = ""): string { return UrlHelper::url($ruta); } }
if (!function_exists("asset")) { function asset(string $ruta): string { return UrlHelper::asset($ruta); } }
if (!function_exists("url_completa")) { function url_completa(string $ruta = ""): string { return UrlHelper::urlCompleta($ruta); } }
if (!function_exists("redirigir")) { function redirigir(string $ruta = ""): void { UrlHelper::redirigir($ruta); } }
