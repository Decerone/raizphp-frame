<?php
declare(strict_types=1);
namespace App\Nucleo;
class UrlHelper {
    private static ?string $urlBase = null;
    private static ?string $urlBaseCompleta = null;
    public static function base(): string {
        if (self::$urlBase === null) {
            $scriptDir = dirname($_SERVER["SCRIPT_NAME"]);
            $scriptDir = preg_replace("#/raizphp/public$#", "", $scriptDir);
            self::$urlBase = ($scriptDir === "/" || $scriptDir === "\\\\") ? "" : $scriptDir;
        }
        return self::$urlBase;
    }
    public static function baseCompleta(): string {
        if (self::$urlBaseCompleta === null) {
            $protocolo = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
            self::$urlBaseCompleta = $protocolo . "://" . ($_SERVER["HTTP_HOST"] ?? "localhost") . self::base();
        }
        return self::$urlBaseCompleta;
    }
    public static function asset(string $ruta = ""): string { return dirname($_SERVER["SCRIPT_NAME"]) . "/" . ltrim($ruta, "/"); }
    public static function url(string $ruta = ""): string { return self::base() . "/" . ltrim($ruta, "/"); }
    public static function urlCompleta(string $ruta = ""): string { return self::baseCompleta() . "/" . ltrim($ruta, "/"); }
    public static function redirigir(string $ruta = ""): void { header("Location: " . self::url($ruta)); exit; }
}
