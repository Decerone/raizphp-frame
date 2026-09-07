<?php
declare(strict_types=1);
namespace App\Nucleo;
class Enrutador {
    private array $rutas=[],$middlewaresRuta=[]; private PilaMiddleware $pila; private ?Renderizador $renderizador=null;
    public function __construct(){ $this->pila=new PilaMiddleware(); }
    public function setRenderizador(Renderizador $r):void{ $this->renderizador=$r; }
    public function usarMiddleware(Middleware $m):void{ $this->pila->agregar($m); }
    public function agregarRuta(string $met,string $ruta,string $man):void{ $this->rutas[$met][$ruta]=$man; }
    public function agregarMiddlewareRuta(string $met,string $ruta,Middleware $m):void{ $this->middlewaresRuta[$met][$ruta][]=$m; }
    private function obtenerUriBase():string{ $s=$_SERVER["SCRIPT_NAME"]??"/index.php"; $b=dirname(dirname(dirname($s))); if($b==="/"||$b==="\\\\")$b=""; return $b; }
    public function despachar(string $met,string $uri):void{
        try{
            $uri=parse_url($uri,PHP_URL_PATH); $uri=rtrim($uri,"/")?:"/"; $base=$this->obtenerUriBase();
            if($base!==""&&strpos($uri,$base)===0){$uri=substr($uri,strlen($base));$uri="/".ltrim($uri,"/");}
            if($uri===""||$uri===false)$uri="/";
            if(!isset($this->rutas[$met][$uri])){$this->mostrarError(404);return;}
            if(isset($this->middlewaresRuta[$met][$uri])) foreach($this->middlewaresRuta[$met][$uri] as $m) $this->pila->agregar($m);
            [$ctrl,$acc]=explode("@",$this->rutas[$met][$uri]); $cc="App\\\\Controladores\\\\$ctrl";
            if(!class_exists($cc)) throw new \RuntimeException("Controlador $cc no encontrado.");
            $dest=function()use($cc,$acc){$i=new $cc();return call_user_func([$i,$acc]);};
            $this->pila->ejecutar($_SERVER,$dest);
        }catch(\Throwable $e){ error_log("Error 500: ".$e->getMessage()); $this->mostrarError(500); }
    }
    private function mostrarError(int $codigo):void{
        http_response_code($codigo);
        if($this->renderizador!==null){ try{ $this->renderizador->mostrar("errores/$codigo",["urlBase"=>UrlHelper::base()?:"/"],null,false); return; }catch(\RuntimeException $e){} }
        echo match($codigo){404=>"404 - Pagina no encontrada",500=>"500 - Error interno del servidor",default=>"Error ".$codigo};
    }
}
