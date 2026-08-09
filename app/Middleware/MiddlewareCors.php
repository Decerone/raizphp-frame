<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Nucleo\Middleware;
class MiddlewareCors extends Middleware
{
    public function manejar($peticion, callable $siguiente)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        if ($_SERVER['REQUEST_METHOD']==='OPTIONS') { http_response_code(204); return; }
        return $siguiente($peticion);
    }
}
