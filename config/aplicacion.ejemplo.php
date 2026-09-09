<?php
declare(strict_types=1);

return [
    'nombre' => 'Mi Aplicación RaízPHP',
    'entorno' => 'desarrollo',
    'zona_horaria' => 'America/Caracas',
    'api' => [
        'habilitada' => true,
        'prefijo' => '/api'
    ],
    'cache' => [
        'habilitado' => true,
        'duracion' => 3600
    ],
    'seguridad' => [
        'maximos_intentos_login' => 5,
        'tiempo_bloqueo_login' => 60,
        'forzar_https' => false,
        'correo' => [
            'modo' => 'archivo',
            'remitente' => 'no-reply@localhost.local',
            'directorio_prueba' => 'almacenamiento/correos',
            'smtp' => [
                'host' => 'smtp.gmail.com',
                'puerto' => 587,
                'usuario' => '',
                'clave' => '',
                'seguridad' => 'tls'
            ]
        ]
    ]
];
