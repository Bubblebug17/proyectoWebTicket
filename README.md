# SoporteTicket — Sistema de Gestión de Tickets

Aplicación web en **PHP 8.2** con arquitectura **MVC** propia, MySQL y panel de soporte autenticado. Los clientes crean tickets desde un formulario público sin registrarse.

Documentación técnica detallada: [`docs/ARQUITECTURA.md`](docs/ARQUITECTURA.md)

---

## Requisitos del servidor

| Componente | Versión mínima |
|------------|----------------|
| PHP | 8.2+ |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Apache | 2.4+ con `mod_rewrite` |
| Composer | 2.x |

### Extensiones PHP necesarias

- `pdo_mysql`
- `mbstring`
- `fileinfo` (validación MIME de archivos adjuntos)
- `session`

Verificar con:

```bash
php -m
```

---

## Puesta en marcha rápida

Estos pasos aplican en **cualquier entorno** (Windows, Linux o macOS).

### 1. Obtener el código

```bash
git clone <url-del-repositorio> proyectoWebTicket
cd proyectoWebTicket
```

O descomprimir el proyecto en la carpeta pública del servidor web (por ejemplo `htdocs/proyectoWebTicket` o `/var/www/html/proyectoWebTicket`).

### 2. Instalar dependencias

```bash
composer install
```

Genera la carpeta `vendor/` con el autoload PSR-4 (`App\` → `app/`).

### 3. Crear la base de datos

Importar el esquema incluido:

```bash
mysql -u root -p < database/schema.sql
```

O desde **phpMyAdmin**: Importar → seleccionar `database/schema.sql`.

El script crea:

- Base de datos: `ticket_system`
- Tablas: `usuarios`, `tickets`, `mensajes`
- Usuario demo de soporte (ver sección [Acceso de prueba](#acceso-de-prueba))

### 4. Configurar la aplicación

Editar `config/config.php`:

```php
return [
    'database' => [
        'host' => 'localhost',       // Host MySQL
        'dbname' => 'ticket_system',
        'username' => 'root',        // Usuario MySQL del entorno
        'password' => '',            // Contraseña MySQL
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'SoporteTicket',
        'url' => 'http://localhost/proyectoWebTicket',  // URL pública base
        'timezone' => 'America/Lima'                     // Zona horaria PHP
    ],
    'session' => [
        'lifetime' => 3600,
        'name' => 'TICKET_SESSION'
    ]
];
```

Ajustar **`database`** según las credenciales del nuevo entorno y **`app.url`** con la URL real donde se alojará el proyecto.

### 5. Permisos de escritura

El sistema guarda adjuntos en `public/uploads/`. Crear la carpeta si no existe y dar permisos de escritura al usuario del servidor web:

```bash
mkdir -p public/uploads
chmod 775 public/uploads
```

En Linux con Apache:

```bash
sudo chown -R www-data:www-data public/uploads
```

### 6. Apache: URLs amigables

El archivo `.htaccess` en la raíz redirige todas las peticiones a `index.php`. Requisitos:

1. **`mod_rewrite` habilitado**
2. **`AllowOverride All`** en el directorio del proyecto

Ejemplo en `httpd.conf` o virtual host:

```apache
<Directory "/ruta/al/proyectoWebTicket">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Reiniciar Apache después de cambios.

### 7. Verificar instalación

Abrir en el navegador la URL configurada (ejemplo por defecto):

| Recurso | URL |
|---------|-----|
| Formulario público | `http://localhost/proyectoWebTicket` |
| Login soporte | `http://localhost/proyectoWebTicket/login` |
| Dashboard | `http://localhost/proyectoWebTicket/soporte/dashboard` |

**Checklist:**

- [ ] Se muestra el formulario de creación de tickets
- [ ] Login con usuario demo funciona
- [ ] Dashboard lista tickets (vacío o con datos)
- [ ] Crear un ticket y subir un adjunto funciona
- [ ] CSS carga correctamente (`/proyectoWebTicket/public/css/app.css`)

---

## Cambiar la ruta o dominio del proyecto

Si el proyecto **no** se instala en `/proyectoWebTicket`, hay que alinear la ruta base en varios puntos. Ejemplo: mover a `https://tickets.midominio.com/` (raíz) o a `https://midominio.com/soporte/`.

| Archivo | Qué cambiar |
|---------|-------------|
| `config/config.php` | `app.url` → URL pública completa |
| `.htaccess` | `RewriteBase /proyectoWebTicket/` → tu subcarpeta o `/` |
| `app/Core/Request.php` | `$basePath = '/proyectoWebTicket'` → misma ruta |
| `app/Core/FileUpload.php` | Ruta en `getPublicUrl()` |
| Vistas, controladores y middleware | Enlaces y redirecciones con prefijo `/proyectoWebTicket` |

Buscar todas las ocurrencias:

```bash
# Linux / macOS / Git Bash
grep -r "proyectoWebTicket" app/ config/ public/

# Windows PowerShell
Select-String -Path app\*,config\* -Pattern "proyectoWebTicket" -Recurse
```

Reemplazar `/proyectoWebTicket` por la ruta base del nuevo entorno (por ejemplo `/soporte` o dejar vacío si está en la raíz del dominio).

> **Recomendación:** si el despliegue es en producción con dominio propio, centralizar la URL base en `config/config.php` y usarla en vistas/controladores en una iteración futura.

---

## Instalación en Windows (XAMPP)

### Requisitos

1. [XAMPP](https://www.apachefriends.org/) con PHP 8.2 (ej. `C:\xampp82\`)
2. [Composer](https://getcomposer.org/download/) apuntando al PHP de XAMPP:
   ```
   C:\xampp82\php\php.exe
   ```

### Pasos

```powershell
cd C:\xampp82\htdocs\proyectoWebTicket
composer install
mkdir public\uploads -Force
```

1. Iniciar **Apache** y **MySQL** desde el panel de XAMPP
2. Importar `database/schema.sql` en phpMyAdmin (`http://localhost/phpmyadmin`)
3. Editar `config/config.php` (credenciales MySQL; en XAMPP la contraseña de `root` suele estar vacía)
4. Acceder a `http://localhost/proyectoWebTicket`

Si Composer usa otra versión de PHP:

```powershell
C:\xampp82\php\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install
```

---

## Instalación en Linux

### Paquetes (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl \
                 apache2 mysql-server composer
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Despliegue

```bash
sudo mkdir -p /var/www/html/proyectoWebTicket
cd /var/www/html/proyectoWebTicket
composer install
mkdir -p public/uploads
sudo chown -R www-data:www-data .
sudo chmod -R 775 public/uploads
mysql -u root -p < database/schema.sql
```

Virtual host opcional (`/etc/apache2/sites-available/proyectoWebTicket.conf`):

```apache
<VirtualHost *:80>
    ServerName tickets.local
    DocumentRoot /var/www/html/proyectoWebTicket

    <Directory /var/www/html/proyectoWebTicket>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
sudo a2ensite proyectoWebTicket.conf
sudo systemctl reload apache2
```

---

## Acceso de prueba

Tras importar `database/schema.sql`:

| Rol | Email | Contraseña |
|-----|-------|------------|
| Soporte | `soporte@demo.com` | `password` |

Los clientes **no** tienen cuenta. Crean tickets desde el formulario público.

### Datos de ejemplo (opcional)

Script auxiliar para poblar tickets de prueba:

```bash
php seed_tickets.php
```

Ejecutar solo en entornos de desarrollo.

---

## Ejecutar pruebas

El proyecto incluye tests unitarios con PHPUnit (phar local, sin dependencia extra en `composer.json`):

```bash
# Descargar PHPUnit (primera vez)
composer run test:install

# Ejecutar suite unitaria
composer test
# o
composer run test:unit
```

Configuración: `phpunit.xml` — tests en `tests/Unit/`.

---

## Estructura del proyecto

```
proyectoWebTicket/
├── app/
│   ├── Controllers/      # Controladores HTTP
│   ├── Core/             # Application, Router, Request, Database, Session…
│   │   └── Middleware/   # AuthMiddleware
│   ├── Entities/         # Usuario, Ticket, Mensaje
│   ├── Models/           # Acceso a datos (PDO)
│   └── Views/            # Plantillas PHP
├── config/
│   └── config.php        # Configuración BD, app y sesión
├── database/
│   └── schema.sql        # Esquema MySQL
├── docs/
│   └── ARQUITECTURA.md   # Informe técnico del sistema
├── public/
│   ├── css/app.css       # Estilos globales
│   ├── js/               # Validación de formularios, upload
│   └── uploads/          # Adjuntos de tickets (escritura requerida)
├── tests/Unit/           # Pruebas PHPUnit
├── tools/                # Scripts PHPUnit (install-phpunit.php)
├── index.php             # Punto de entrada
├── .htaccess             # Rewrite → index.php
├── composer.json
└── phpunit.xml
```

---

## Rutas principales

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/` | Formulario público de tickets |
| POST | `/ticket/create` | Enviar ticket |
| GET | `/login` | Login soporte |
| POST | `/login` | Autenticación |
| GET | `/logout` | Cerrar sesión |
| GET | `/soporte/dashboard` | Dashboard con resumen y listado |
| GET | `/soporte/tickets` | Listado completo |
| GET | `/soporte/tickets/view?id={id}` | Detalle y mensajes |
| POST | `/soporte/tickets/responder` | Responder ticket |
| POST | `/soporte/tickets/cambiar-estado` | Cambiar estado |
| GET | `/soporte/reportes` | Reportes gráficos |

Todas las rutas de soporte requieren sesión con rol `soporte`.

---

## Solución de problemas

### `Class not found` o errores de autoload

```bash
composer install
composer dump-autoload
```

### `404 - Página no encontrada`

- Verificar que Apache tenga `mod_rewrite` activo
- Confirmar que `.htaccess` existe en la raíz del proyecto
- Revisar `AllowOverride All` en la configuración del virtual host
- Comprobar que `RewriteBase` en `.htaccess` coincide con la subcarpeta del proyecto

### Error de conexión a MySQL

- Servicio MySQL en ejecución
- Credenciales correctas en `config/config.php`
- Base de datos `ticket_system` creada e importada

### CSS o JS no cargan

- Verificar que la ruta base `/proyectoWebTicket` en las vistas coincida con la URL real
- Comprobar que `public/css/app.css` y `public/js/` son accesibles vía navegador

### No se pueden subir archivos

- Carpeta `public/uploads/` existe
- Permisos de escritura para el usuario de Apache (`www-data`, `daemon`, etc.)
- Límite de PHP: `upload_max_filesize` y `post_max_size` ≥ 10M

### Login correcto pero redirección fallida

- Revisar prefijo `/proyectoWebTicket` en `AuthController`, `AuthMiddleware` y redirecciones del router

---

## Funcionalidades

### Formulario público

- Creación de tickets sin registro
- Campos: nombre, apellido, correo, celular, tipo de conexión (ISP), problema, descripción
- Adjuntos: imágenes y vídeos (máx. 10 MB) con vista previa
- Validación en cliente (`public/js/form-validation.js`) y servidor

### Panel de soporte

- Dashboard con contadores por estado
- Listado paginado y detalle de tickets
- Respuestas vía mensajes
- Cambio de estado: `abierto`, `en_proceso`, `cerrado`
- Asignación automática al responder un ticket sin agente
- Reportes con gráficos (Highcharts)

---

## Licencia

MIT License — ver [`LICENSE`](LICENSE).
