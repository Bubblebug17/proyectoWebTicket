# Intranet Tambo — Tickets de sucursal

Aplicación web en **PHP 8.2** con arquitectura **MVC** propia y MySQL. El personal de tienda registra tickets desde la intranet. Soporte los atiende y los deriva al encargado de cada área: Logístico, Tecnológico, Administrativo u Otros.

La URL de desarrollo es `http://localhost/proyectoWebTicket`.

---

## Requisitos

| Componente | Versión mínima |
| --- | --- |
| PHP | 8.2+ |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Apache | 2.4+ con `mod_rewrite` |
| Composer | 2.x |

Extensiones PHP: `pdo_mysql`, `mbstring`, `fileinfo` y `session`.

```bash
php -m
```

---

## Levantar la instancia

Estos pasos dejan la aplicación lista para entrar con los usuarios de demostración. En XAMPP con la contraseña de `root` vacía, `config/config.php` ya apunta a esa base y no hace falta editarlo.

### 1. Colocar el código en el servidor web

```bash
git clone https://github.com/Bubblebug17/proyectoWebTicket/ proyectoWebTicket
cd proyectoWebTicket
```

La carpeta debe quedar publicada por Apache. En XAMPP la ruta habitual es `C:\xampp82\htdocs\proyectoWebTicket`. En Linux, `/var/www/html/proyectoWebTicket`.

### 2. Instalar el autoload

```bash
composer install
```

Crea `vendor/` con el autoload PSR-4 (`App\` → `app/`).

Si Composer usa otro PHP distinto del de XAMPP:

```powershell
C:\xampp82\php\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install
```

### 3. Crear la base de datos

Iniciar **Apache** y **MySQL**. Luego importar el esquema una sola vez:

```bash
mysql -u root -p < database/schema.sql
```

En XAMPP, con `root` sin contraseña:

```powershell
C:\xampp82\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < database\schema.sql
```

También se puede importar `database/schema.sql` desde phpMyAdmin (`http://localhost/phpmyadmin`).

El script crea la base `ticket_system` y estas tablas:

- `sucursales`: TB-MIR Miraflores, TB-SBO San Borja y TB-OLI Los Olivos
- `usuarios`: perfiles de tienda, soporte y un encargado por área
- `tipos_ticket` y `subtipos_ticket`: 4 áreas y 6 motivos por área
- `tickets` y `mensajes`: vacías hasta que se registre un ticket o se ejecute el seeder

`schema.sql` usa `CREATE TABLE IF NOT EXISTS`. Si `ticket_system` ya existe de una versión anterior, no actualiza columnas viejas. Para una instancia limpia:

```sql
DROP DATABASE IF EXISTS ticket_system;
```

y volver a importar el archivo.

### 4. Revisar la configuración

`config/config.php` de desarrollo:

```php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'ticket_system',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Intranet Tambo',
        'url' => 'http://localhost/proyectoWebTicket',
        'timezone' => 'America/Lima'
    ],
    'session' => [
        'lifetime' => 3600,
        'name' => 'TICKET_SESSION'
    ]
];
```

Cambiar usuario, contraseña y `app.url` si el entorno no es el de XAMPP local.

### 5. Carpeta de adjuntos

Los archivos del formulario se guardan en `public/uploads/`.

```bash
mkdir -p public/uploads
chmod 775 public/uploads
```

En Windows:

```powershell
mkdir public\uploads -Force
```

En Linux, el usuario de Apache debe poder escribir ahí:

```bash
sudo chown -R www-data:www-data public/uploads
sudo chmod 775 public/uploads
```

### 6. Comprobar Apache

El `.htaccess` de la raíz envía las rutas a `index.php`. Hacen falta `mod_rewrite` y `AllowOverride All` sobre la carpeta del proyecto.

```apache
<Directory "/ruta/al/proyectoWebTicket">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

En XAMPP, `mod_rewrite` suele estar activo y `htdocs` ya permite `.htaccess`. Si una ruta devuelve 404 de Apache, revisar `httpd.conf` y reiniciar Apache.

### 7. Abrir la intranet

| Recurso | URL |
| --- | --- |
| Entrada | http://localhost/proyectoWebTicket |
| Login | http://localhost/proyectoWebTicket/login |
| Nuevo ticket | http://localhost/proyectoWebTicket/ticket/create |
| Dashboard de soporte | http://localhost/proyectoWebTicket/soporte/dashboard |

La raíz redirige al login o al inicio del perfil que ya tenga sesión.

---

## Acceso de prueba

La contraseña de todos es `password`.

| Perfil | Área | Correo | Al ingresar |
| --- | --- | --- | --- |
| Responsable de tienda | — | `tienda@demo.com` | Formulario de ticket |
| Soporte | — | `soporte@demo.com` | Dashboard |
| Responsable de área | Logístico | `area@demo.com` | Mis tickets |
| Responsable de área | Tecnológico | `tecnologia@demo.com` | Mis tickets |
| Responsable de área | Administrativo | `administracion@demo.com` | Mis tickets |
| Responsable de área | Otros | `otros@demo.com` | Mis tickets |

La tienda pertenece a Tambo Miraflores. Soporte ve todos los tickets, los deriva al encargado del área y consulta reportes. Cada encargado solo gestiona los tickets que le derivaron.

### Tickets de ejemplo

Opcional, solo en desarrollo. Inserta 80 tickets con mensajes:

```bash
php database/seed_tickets_mensajes.php
```

Otras formas:

```bash
php database/seed_tickets_mensajes.php --count=40
php database/seed_tickets_mensajes.php --fresh
```

`--fresh` vacía `tickets` y `mensajes` antes de cargar. Sin esa opción, los tickets se agregan a los que ya existan.

En XAMPP:

```powershell
C:\xampp82\php\php.exe database\seed_tickets_mensajes.php
```

---

## Otra ruta o dominio

Si la aplicación no vive en `/proyectoWebTicket`, hay que cambiar el prefijo en todos estos puntos:

| Archivo | Qué cambiar |
| --- | --- |
| `config/config.php` | `app.url` |
| `.htaccess` | `RewriteBase /proyectoWebTicket/` |
| `app/Core/Request.php` | `$basePath = '/proyectoWebTicket'` |
| Vistas y controladores | Enlaces y redirecciones que empiezan por `/proyectoWebTicket` |
| `app/Views/soporte/view_ticket.php` | URL de los archivos en `public/uploads` |

```bash
grep -r "proyectoWebTicket" app/ config/ public/ .htaccess
```

En PowerShell:

```powershell
Get-ChildItem -Recurse -Include *.php,*.htaccess,*.css,*.js | Select-String "proyectoWebTicket"
```

En la raíz del dominio, `RewriteBase` pasa a `/` y `$basePath` queda vacío.

---

## Instalación en Linux

```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl \
                 apache2 mysql-server composer
sudo a2enmod rewrite
sudo systemctl restart apache2
```

```bash
sudo mkdir -p /var/www/html/proyectoWebTicket
cd /var/www/html/proyectoWebTicket
composer install
mkdir -p public/uploads
sudo chown -R www-data:www-data .
sudo chmod -R 775 public/uploads
mysql -u root -p < database/schema.sql
```

Virtual host opcional en `/etc/apache2/sites-available/proyectoWebTicket.conf`:

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

Agregar `127.0.0.1 tickets.local` en `/etc/hosts` y abrir `http://tickets.local`. En ese caso también hay que ajustar el prefijo de la sección anterior, porque el `.htaccess` sigue esperando `/proyectoWebTicket`.

---

## Pruebas

```bash
php tools/install-phpunit.php
composer test
```

O directamente:

```bash
php tools/phpunit.phar --do-not-cache-result
```

---

## Estructura

```
proyectoWebTicket/
├── app/
│   ├── Controllers/       # Auth, tickets, soporte y reportes
│   ├── Core/              # Application, Router, Request, Database, Session
│   │   └── Middleware/    # AuthMiddleware
│   ├── Entities/          # Usuario, Ticket, Mensaje
│   ├── Models/            # PDO
│   └── Views/             # Plantillas PHP
├── config/config.php
├── database/
│   ├── schema.sql         # Instalación nueva, sin tickets
│   └── seed_tickets_mensajes.php
├── public/
│   ├── css/app.css
│   ├── img/               # Logo y sello Tambo
│   ├── js/
│   └── uploads/           # Adjuntos
├── tests/Unit/
├── tools/                 # PHPUnit
├── index.php
├── .htaccess
├── composer.json
└── phpunit.xml
```

---

## Rutas

El prefijo `/proyectoWebTicket` se omite en esta tabla.

| Método | Ruta | Quién | Descripción |
| --- | --- | --- | --- |
| GET | `/` | Público | Redirige al login o al inicio del perfil |
| GET, POST | `/login` | Público | Ingreso a la intranet |
| GET | `/logout` | Sesión | Cierra la sesión |
| GET, POST | `/ticket/create` | Todos los perfiles | Registrar un ticket |
| GET | `/soporte/dashboard` | Soporte | Resumen y últimos tickets |
| GET | `/soporte/tickets` | Soporte y encargado | Listado. El encargado ve solo los suyos |
| GET | `/soporte/tickets/view?id={id}` | Soporte y encargado asignado | Detalle y mensajes |
| POST | `/soporte/tickets/derivar` | Soporte | Deriva el ticket al encargado del área |
| POST | `/soporte/tickets/responder` | Soporte y encargado asignado | Responde el ticket |
| POST | `/soporte/tickets/cambiar-estado` | Soporte y encargado asignado | Cambia el estado |
| GET | `/soporte/reportes` | Soporte | Reportes |
| GET | `/reportes/exportarExcel` | Soporte | Exportación Excel |
| GET | `/reportes/exportarPDF` | Soporte | Vista de impresión |

---

## Licencia

MIT License — ver [LICENSE](LICENSE).
