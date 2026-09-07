<?php
declare(strict_types=1);
namespace App\Nucleo;
class Renderizador {
    private string $directorioVistas;
    public function __construct(string $directorioVistas){ $this->directorioVistas=$directorioVistas; }
    public function mostrar(string $vista,array $datos=[],?string $plantilla="plantilla",bool $usarCache=true):void{
        extract($datos); $rv=$this->directorioVistas."/$vista.php";
        if(!file_exists($rv)) throw new \RuntimeException("Vista $vista no encontrada.");
        
        // No cachear vistas con formularios CSRF
        if($usarCache && strpos(file_get_contents($rv),"_token")!==false){ $usarCache=false; }
        
        $cache=new Cache(); $claveCache=Cache::claveVista($vista,$datos);
        if($usarCache){ $cc=$cache->obtener($claveCache); if($cc!==null){ echo $cc; return; } }
        ob_start(); require $rv; $contenido=ob_get_clean();
        if($plantilla){ $rp=$this->directorioVistas."/$plantilla.php"; if(file_exists($rp)){ ob_start(); require $rp; $contenido=ob_get_clean(); } }
        if($usarCache) $cache->guardar($claveCache,$contenido);
        echo $contenido;
    }
}
