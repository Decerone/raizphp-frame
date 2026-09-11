# 🚀 Guía de Deploy — RaízPHP v2.1

Guía paso a paso para desplegar RaízPHP en un servidor de producción.

> ⚠️ **Antes de empezar:** RaízPHP está en desarrollo activo.

---

## 📋 Checklist previo

- [ ] Servidor con PHP 8.0+
- [ ] Extensiones: pdo, openssl, mbstring, json
- [ ] Apache 2.4+ con mod_rewrite
- [ ] Certificado SSL
- [ ] Base de datos creada
- [ ] Acceso SSH

---

## 1. Configurar el DocumentRoot

**Debe apuntar a public/, NUNCA a la raíz.**

Edita /etc/apache2/sites-available/mi-proyecto.conf:

```apache
<VirtualHost *:443>
    ServerName mi-dominio.com
    DocumentRoot /var/www/mi-proyecto/raizphp/public
    SSLEngine on
</VirtualHost>
```

Activar:

```bash
sudo a2enmod rewrite ssl headers
sudo systemctl reload apache2
```

---

## 2. Permisos de archivos

```bash
sudo chown -R www-data:www-data /var/www/mi-proyecto
sudo find /var/www/mi-proyecto -type d -exec chmod 775 {} \;
sudo find /var/www/mi-proyecto -type f -exec chmod 664 {} \;
sudo chmod +x /var/www/mi-proyecto/raizphp/raiz
sudo chmod -R 775 /var/www/mi-proyecto/raizphp/almacenamiento
sudo chmod 640 /var/www/mi-proyecto/raizphp/config/*.php
```

**Importante:** config/ debe tener permisos 640 porque contiene credenciales.

---

## 3. Configuración de la aplicación

Edita config/aplicacion.php:

```php
return [
    entorno => produccion,
    seguridad => [
        forzar_https => true,
        correo => [modo => smtp]
    ]
];
```

Verificar:

```bash
php raiz proyecto:info
```

---

## 4. Bloquear el instalador

```bash
sudo rm /var/www/mi-proyecto/raizphp/public/instalar.php
```

El instalador ya crea instalado.lock, así que no se puede reinstalar.

---

## 5. Verificar el despliegue

```bash
cd /var/www/mi-proyecto/raizphp
bash tests/humo.sh
```

Debe mostrar todo OK.

---

## 6. Backup automático

sudo crontab -e:

```cron
0 3 * * * cd /var/www/mi-proyecto/raizphp && php raiz db:respaldar
```

---

## 7. Logs

```bash
php raiz log:ver 100
tail -f almacenamiento/logs/error-$(date +