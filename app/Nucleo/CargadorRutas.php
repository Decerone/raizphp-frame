<?php
declare(strict_types=1);
namespace App\Nucleo;
class CargadorRutas {
    private string $directorioRutas;
    private Enrutador $enrutador;
    public function __construct(Enrutador $enrutador, string $directorioRutas) { $this->enrutador=$enrutador; $this->directorioRutas=$directorioRutas; }
    public function cargar(?string $archivo=null): void {
        if($archivo!==null){ $this->cargarArchivo($this->directorioRutas."/".$archivo.".php"); }
        else{ foreach(glob($this->directorioRutas."/*.php") as $f) $this->cargarArchivo($f); }
    }
    private function cargarArchivo(string $f): void {
        if(!file_exists($f)) throw new \RuntimeException("Archivo de rutas no encontrado: $f");
        $cb=require $f; if(!is_callable($cb)) throw new \RuntimeException("El archivo de rutas debe retornar una funcion: $f");
        $cb($this->enrutador);
    }
}
