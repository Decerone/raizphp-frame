# 🌱 RaízPHP v2.1

Framework MVC para PHP 8.0+ con ORM nativo, API REST, Panel Admin, CLI con 30+ comandos y **cero dependencias externas**.

Nombrado en español. Hecho en Costa Rica. 🇨🇷

[![PHP 8.0+](https://img.shields.io/badge/PHP-8.0%2B-777bb4.svg)](https://php.net)
[![0 Dependencias](https://img.shields.io/badge/Dependencias-0-brightgreen.svg)](#)
[![Estado](https://img.shields.io/badge/Estado-Desarrollo-orange.svg)](#)

---

## ⚠️ Estado del proyecto

**RaízPHP está en DESARROLLO ACTIVO. NO usar en producción todavía.**

| Aspecto | Estado |
|---|---|
| Estable | ❌ No |
| Producción | ❌ No |
| Desarrollo activo | ✅ Sí |
| Próxima versión | v2.2.0 |

Este framework está en auditoría técnica. El núcleo funciona, los tests pasan, pero no ha sido probado en entornos de producción reales.

---

## ✅ Qué ES RaízPHP

- Framework **MVC** completo
- **ORM nativo** (NaturalORM) con 30+ métodos en español
- **API REST** con autenticación por tokens Bearer
- **Panel de administración** con gestión de usuarios y caché
- **CLI** (RaízCLI) con 30+ comandos
- **Sistema de caché** con invalidación automática
- **Multi-motor**: MySQL, PostgreSQL, SQLite
- **10 capas de seguridad** (CSRF, XSS, SQLi, bcrypt, rate limiting, etc.)
- **Framework CSS** propio (CelajeCSS)
- **0 dependencias externas** (sin Composer, sin NPM)
- **Tests nativos** (sin PHPUnit)

---

## ❌ Qué NO es RaízPHP

- No es un clon de Laravel ni Symfony
- No usa Composer ni paquetes de Packagist
- No requiere NPM ni build de frontend
- No trae autenticación de dos factores (2FA)
- No incluye sistema de colas ni WebSockets
- No ha sido probado en producción real

---

## 📋 Requisitos

| Componente | Requisito |
|---|---|
| PHP | 8.0 o superior |
| Extensiones | PDO, OpenSSL, mbstring, JSON |
| Extensiones BD | pdo_mysql (MySQL) o pdo_pgsql (PostgreSQL) o pdo_sqlite (SQLite) |
| Servidor | Apache 2.4+ con mod_rewrite (o nginx equivalente) |
| Base de datos | MySQL 5.7+ / PostgreSQL 10+ / SQLite 3+ |

---

## 🚀 Instalación

### 1. Clonar el repositorio

    git clone https://github.com/Decerone/raizphp-frame.git
    cd raizphp-frame

### 2. Copiar al servidor web

    sudo mkdir -p /var/www/mi-proyecto/raizphp
    sudo cp -r * /var/www/mi-proyecto/raizphp/
    sudo cp .gitignore /var/www/mi-proyecto/raizphp/

### 3. Copiar configuración de ejemplo

    cd /var/www/mi-proyecto/raizphp
    sudo cp config/aplicacion.ejemplo.php config/aplicacion.php
    sudo cp config/base_datos.ejemplo.php config/base_datos.php

### 4. Permisos

    sudo chown -R www-data:www-data /var/www/mi-proyecto
    sudo find /var/www/mi-proyecto -type d -exec chmod 775 {} \;
    sudo find /var/www/mi-proyecto -type f -exec chmod 664 {} \;
    sudo chmod +x /var/www/mi-proyecto/raizphp/raiz
    sudo chmod -R 775 /var/www/mi-proyecto/raizphp/almacenamiento

### 5. Ejecutar el instalador

Abre en el navegador:

    http://localhost/mi-proyecto/raizphp/public/instalar.php

Sigue los 3 pasos y anota las credenciales.

**⚠️ IMPORTANTE:** Después de instalar, **elimina public/instalar.php** del servidor.

### 6. Verificar

    php raiz version
    php raiz ayuda

---

## 📁 Estructura del proyecto

    raizphp/
    ├── raiz                       # CLI ejecutable
    ├── raizphp.json               # Manifiesto del framework
    ├── app/
    │   ├── Controladores/         # Controladores de la aplicación
    │   │   ├── Admin/             # Controladores del panel admin
    │   │   └── Api/               # Controladores de la API REST
    │   ├── Middleware/            # Middlewares (8 incluidos)
    │   ├── Migraciones/           # Migraciones de BD
    │   ├── Modelos/               # Modelos NaturalORM
    │   ├── Nucleo/                # Núcleo del framework
    │   ├── rutas/                 # Rutas por módulos
    │   ├── Seeders/               # Datos de prueba
    │   └── Vistas/                # Vistas HTML
    ├── almacenamiento/            # cache, logs, correos, backups
    ├── config/                    # Configuración (NO en Git)
    ├── public/                    # Front controller y assets
    │   ├── index.php              # Punto de entrada
    │   ├── instalar.php           # Instalador web
    │   └── estilos/celaje.css     # Framework CSS
    └── tests/                     # Tests nativos
        ├── ejecutar.php           # Runner de tests
        └── humo.sh                # Script de humo

---

## 📖 Ejemplo mínimo

### 1. Crear controlador, modelo y vista con el CLI

    php raiz crear:controlador Producto
    php raiz crear:modelo Producto
    php raiz crear:vista productos/index

### 2. Registrar ruta en app/rutas/web.php

    $enrutador->agregarRuta('GET', '/productos', 'ProductoControlador@index');

### 3. Controlador (app/Controladores/ProductoControlador.php)

    <?php
    declare(strict_types=1);
    namespace App\Controladores;
    
    use App\Nucleo\ControladorBase;
    use App\Modelos\Producto;
    
    class ProductoControlador extends ControladorBase
    {
        public function index(): void
        {
            $productos = Producto::todos();
            $this->renderizar('productos/index', [
                'titulo' => 'Productos',
                'productos' => $productos
            ]);
        }
    }

### 4. Vista (app/Vistas/productos/index.php)

    <h1><?= e($titulo) ?></h1>
    <ul>
        <?php foreach ($productos as $p): ?>
            <li><?= e($p->nombre) ?> — $<?= e((string)$p->precio) ?></li>
        <?php endforeach; ?>
    </ul>

---

## 🔒 Seguridad

RaízPHP implementa **10 capas de seguridad** por defecto:

| Capa | Implementación |
|---|---|
| CSRF | MiddlewareCsrf + HelperCsrf::campoOculto() |
| XSS | Helper e() = htmlspecialchars() |
| SQL Injection | PDO con consultas preparadas |
| Mass assignment | $rellenables en modelos |
| Session fixation | session_regenerate_id(true) |
| Rate limiting | LimitadorIntentos con techo exponencial |
| Clickjacking | X-Frame-Options: DENY |
| MIME sniffing | X-Content-Type-Options: nosniff |
| Cookies seguras | HttpOnly, SameSite=Lax |
| Passwords | bcrypt con cost 12 |

### Reglas de seguridad

- **NUNCA** uses GET para mutaciones (borrar, actualizar, logout). Siempre POST con token CSRF.
- **NUNCA** uses unserialize(). Usa json_decode().
- **NUNCA** expongas password ni api_token en JSON.
- **NUNCA** concatenes valores en SQL. Usa ->donde('col', '=', $valor).
- **SIEMPRE** escapa output HTML con e() o htmlspecialchars().

---

## 💻 CLI — Comandos disponibles

    # Ayuda y versión
    php raiz ayuda
    php raiz version
    
    # Crear archivos (soporta lotes con coma)
    php raiz crear:controlador Nombre
    php raiz crear:modelo Nombre
    php raiz crear:vista ruta/vista
    php raiz crear:middleware Nombre
    php raiz crear:ruta nombre
    php raiz crear:api Nombre
    php raiz crear:admin Nombre
    php raiz crear:migracion nombre
    php raiz crear:seeder Nombre
    
    # Ejemplo con lotes
    php raiz crear:modelo Casa,Carro,Moto
    
    # Base de datos
    php raiz migrar
    php raiz revertir
    php raiz migrar:estado
    php raiz seed
    php raiz db:respaldar
    php raiz db:restaurar archivo.sql
    php raiz db:consulta "SELECT * FROM usuarios"
    
    # Caché y logs
    php raiz cache:limpiar
    php raiz cache:estado
    php raiz log:limpiar
    php raiz log:ver 50
    
    # Listar
    php raiz lista:rutas
    php raiz lista:middlewares
    php raiz lista:controladores
    
    # Usuarios
    php raiz usuario:crear
    php raiz usuario:rol email@example.com admin
    php raiz token:generar email@example.com
    php raiz clave:generar
    
    # Servidor de desarrollo
    php raiz servir 8000

### ⚠️ Comandos solo disponibles en entorno=desarrollo

- php raiz crear:tabla (con confirmación)
- php raiz cargar:sql (con confirmación)

---

## 🧪 Tests

RaízPHP incluye un runner de tests nativo (sin PHPUnit, sin Composer).

    # Ejecutar suite completa
    php tests/ejecutar.php
    
    # Script de humo
    bash tests/humo.sh

**Cobertura actual:**
- Validador (email, requerido, min/max numérico y string)
- ConstructorConsulta (identificadores SQL, WHERE obligatorio)
- HelperCsrf (generación, validación, campo oculto)
- Enrutador (rutas exactas, parámetros)
- Usuario (mass assignment, campos ocultos)

**Añadir un test:**

Crea tests/Nucleo/MiClaseTest.php:

    <?php
    declare(strict_types=1);
    
    class MiClaseTest
    {
        public function testAlgoFunciona(): void
        {
            Ayuda::igual(2, 1 + 1);
            Ayuda::verdadero(true);
            Ayuda::lanzaExcepcion(fn() => throw new Exception('test'));
        }
    }

El runner detecta automáticamente cualquier archivo *Test.php dentro de tests/.

---

## 📜 Licencia

**RaízPHP License v1.0** — Licencia propia basada en la filosofía de las 4 libertades, con atribución obligatoria.

📄 [Ver licencia completa (Español/English)](LICENSE.txt)

### Atribución requerida

Todo proyecto que use RaízPHP debe incluir de forma visible en su documentación:

> Este proyecto utiliza RaízPHP v2.1, framework PHP creado por
> Decerone (Costa Rica). https://github.com/Decerone/raizphp-frame

**Esta licencia NO es aprobada por la FSF** (Free Software Foundation) ni la OSI (Open Source Initiative). Es una licencia propia que permite:

- ✅ Uso comercial
- ✅ Modificación
- ✅ Distribución
- ✅ Sublicenciar
- ⚠️ Atribución obligatoria

---

## 👤 Autor

**Decerone** — Costa Rica 🇨🇷

- GitHub: [@Decerone](https://github.com/Decerone)
- Repositorio: [raizphp-frame](https://github.com/Decerone/raizphp-frame)

---

## 🌱 Filosofía

RaízPHP nace de una idea simple: **un framework PHP en español, sin dependencias, que se pueda entender y modificar por completo**.

- **Español primero**: donde(), obtener(), guardar(), encontrar()
- **Cero dependencias**: todo el framework es código propio
- **Simple**: menos magia, más claridad
- **Honesto**: no promete lo que no cumple

---

**🌱 RaízPHP v2.1 — Framework PHP en español, multiplataforma, sin dependencias.**
