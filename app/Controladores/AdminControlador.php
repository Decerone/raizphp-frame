<?php
declare(strict_types=1);
namespace App\Controladores;
use App\Nucleo\ControladorBase;
use App\Nucleo\Autenticacion;
use App\Nucleo\Cache;
use App\Modelos\Usuario;
class AdminControlador extends ControladorBase
{
    private function datosAdmin(): array { return ['usuarioAdmin'=>Autenticacion::obtenerUsuario(),'rutaActual'=>parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)]; }
    
    public function index(): void
    {
        $usuarios = Usuario::todos(); $totalAdmins = 0;
        foreach ($usuarios as $u) if ($u->rol==='admin') $totalAdmins++;
        $cache = new Cache(); $est = $cache->estadisticas();
        $this->renderizarAdmin('admin/inicio',[
            'titulo'=>'Dashboard',
            'totalUsuarios'=>count($usuarios),
            'totalAdmins'=>$totalAdmins,
            'cacheActivo'=>true,
            // CORREGIDO: usar 'archivos' en lugar de 'total_archivos'
            'archivosCache'=>$est['archivos'],
            'adminBase'=>$this->urlAdminBase()
        ]);
    }
    
    public function cache(): void { 
        $cache = new Cache(); 
        $this->renderizarAdmin('admin/cache',[
            'titulo'=>'Caché',
            'estadisticas'=>$cache->estadisticas(),
            'adminBase'=>$this->urlAdminBase()
        ]); 
    }
    
    // CORREGIDO: usar limpiarTodo() en lugar de limpiar()
    public function limpiarCache(): void { 
        (new Cache())->limpiarTodo(); 
        header('Location: '.$this->urlAdminBase().'/cache?limpiado=1'); 
        exit; 
    }
    
    private function renderizarAdmin(string $vista, array $datos = []): void
    {
        $scriptName = $_SERVER['SCRIPT_NAME']??'/index.php'; $urlBase = rtrim(dirname(dirname(dirname($scriptName))), '/');
        if ($urlBase==='/'||$urlBase==='\\') $urlBase = '';
        $datos['urlBase'] = $urlBase; $datos = array_merge($this->datosAdmin(), $datos);
        $renderizador = new \App\Nucleo\Renderizador(\App\Nucleo\Aplicacion::obtenerInstancia()->obtenerDirectorioRaiz().'/app/Vistas');
        $renderizador->mostrar($vista, $datos, 'admin/plantilla');
    }
    
    private function urlAdminBase(): string { $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); return (($s==='/'||$s==='\\')?'':$s).'/admin'; }
}
