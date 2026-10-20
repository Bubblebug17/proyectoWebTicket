# Intranet Tambo — Tickets de sucursal

Aplicación web en **PHP 8.2** con arquitectura **MVC** propia y MySQL. El personal de tienda registra tickets desde la intranet. Soporte los atiende y los deriva al encargado de cada área: Logístico, Tecnológico, Administrativo u Otros.

La URL de desarrollo es `http://localhost/proyectoWebTicket`.

---

## Requisitos

| Componente | Versión mínima |
| --- | --- |
| PHP | 8.2+ |
| MySQL / MariaDB | 5.7+ / 10.3+, remoto |
| Apache | 2.4+ con `mod_rewrite` |
| Composer | 2.x, opcional |

Extensiones PHP: `pdo_mysql`, `mbstring`, `fileinfo` y `session`.

```bash
php -m
```

---

## Levantar la instancia

La base de datos es remota. El host, el nombre y el usuario están en `config/config.php`. No hace falta un MySQL local.

### Windows — instalar desde cero (recomendado)

**1. Clonar el repositorio (manual)**

```batch
git clone https://github.com/Bubblebug17/proyectoWebTicket.git
cd proyectoWebTicket
```

**2. Ejecutar el instalador**

Doble clic en **`instalar.bat`** (o desde la consola en la raíz del clone):

```batch
instalar.bat
```

Descarga **PHP 8.2** y **Apache 2.4** en `runtime/`, instala dependencias, prepara la **BD remota** (`config/config.php`) y crea `public/uploads`.

**3. Acceder al sistema**

El instalador elige el **primer puerto HTTP libre** entre **80 → 8080 → 8081 → 8082** (valor en `runtime/apache-port.txt`) y abre el navegador en:

`http://127.0.0.1:{puerto}/proyectoWebTicket/login`

**Requisitos en PC nueva:** Windows 10/11, Internet la primera vez, [Visual C++ Redistributable x64](https://aka.ms/vs/17/release/vc_redist.x64.exe).

`instalar.bat` activa modo **standalone** (`runtime/standalone.flag`): usa solo Apache portable, no depende de XAMPP.

Para volver a descargar PHP/Apache:

```powershell
powershell -ExecutionPolicy Bypass -File tools\provision_runtime.ps1 -Force
```

La carpeta `runtime/` no se versiona (`.gitignore`).

### Montar en desarrollo (XAMPP opcional)

**`montar.bat`** hace el mismo núcleo que el instalador. Si **no** existe `runtime/standalone.flag`, puede reutilizar XAMPP en el puerto 80 cuando el proyecto está en `htdocs`.

### Linux / servidor

```bash
git clone https://github.com/Bubblebug17/proyectoWebTicket.git
cd proyectoWebTicket
composer install
php tools/montar.php
```

Publicar con Apache/Nginx apuntando al directorio del clone (ver sección más abajo).

### 2. Instalar el autoload

```bash
composer install
```

Crea `vendor/` con el autoload PSR-4 (`App\` → `app/`).

Si Composer usa otro PHP distinto del de XAMPP:

```powershell
C:\xampp82\php\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install
```

### 3. Preparar la base remota

`montar.bat` ya ejecuta este paso. A mano:

```powershell
C:\xampp82\php\php.exe tools\montar.php
```

Lee `config/config.php`, se conecta a esa base y aplica `database/schema.sql` sin crear otra base de datos. Deja:

- `sucursales`: TB-SAN San Isidro (sede principal), TB-MIR Miraflores (ventas/atención) y TB-LUR Lurín (logística)
- `usuarios`: responsables de tienda/almacén, soporte en San Isidro y un encargado por área
- `tipos_ticket` y `subtipos_ticket`: 4 áreas y 6 motivos por área
- `tickets` y `mensajes`: vacías hasta registrar un ticket o ejecutar el seeder

### 4. Revisar la configuración

La conexión remota está en `config/config.php` (`database`). El prefijo de URL (`/proyectoWebTicket`, raíz del sitio IIS, etc.) se **detecta solo**; solo hace falta tocar `app.base_path` o `app.url` si quieres forzar un valor.

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

| Perfil | Sede / área | Correo | Al ingresar |
| --- | --- | --- | --- |
| Responsable de tienda | Miraflores | `tienda@demo.com` | Formulario de ticket |
| Responsable de almacén | Lurín | `almacen@demo.com` | Formulario de ticket |
| Soporte | San Isidro | `soporte@demo.com` | Dashboard |
| Responsable de área | Logístico · Lurín | `area@demo.com` | Mis tickets |
| Responsable de área | Tecnológico · San Isidro | `tecnologia@demo.com` | Mis tickets |
| Responsable de área | Administrativo · San Isidro | `administracion@demo.com` | Mis tickets |
| Responsable de área | Otros · San Isidro | `otros@demo.com` | Mis tickets |

**Sedes:** San Isidro concentra soporte y las áreas administrativas, financieras y tecnológicas; Miraflores orienta ventas y atención al cliente; Lurín cubre operaciones logísticas, almacén y reposición.

Soporte ve todos los tickets, los deriva al encargado del área y consulta reportes. Cada encargado solo gestiona los tickets que le derivaron.

En la base remota de producción/academia los correos pueden ser `@tambo.pe`. Si las sedes ya están en `sucursales` pero los usuarios siguen en sedes antiguas:

```bash
php tools/aplicar_actualizar_sedes.php
```

El seeder de tickets lee las sedes y responsables de tienda que existan en la base.

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

## Otra ruta, IIS o dominio

Enlaces, formularios, redirecciones y assets usan `app_url()` / `App\Core\AppUrl`: el prefijo se deduce de `SCRIPT_NAME` (subcarpeta o raíz del sitio). No hace falta buscar `/proyectoWebTicket` en el código.

| Entorno | Qué necesitas |
| --- | --- |
| **Apache / XAMPP** | `mod_rewrite` y el `.htaccess` de la raíz del proyecto |
| **IIS** | Copiar el proyecto a `inetpub` (o al sitio que uses), PHP con FastCGI y módulo **URL Rewrite**; incluye `web.config` en la raíz |
| **Forzar prefijo** | En `config/config.php`: `'base_path' => '/mi-app'` |
| **URL pública fija** (correos, etc.) | `'url' => 'https://tickets.midominio.com'` |

En IIS, el **directorio físico del sitio** debe ser la carpeta donde está `index.php` (no solo `public/`). Los estáticos siguen en `public/` y se sirven como `/public/css/...` respecto a esa raíz.

Si algo falla con rutas bonitas, comprueba que las peticiones lleguen a `index.php` (reglas de rewrite) y que `public/uploads` exista con permisos de escritura.

---

## Instalación en Linux

```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl \
                 apache2 composer
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
php tools/montar.php
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

Agregar `127.0.0.1 tickets.local` en `/etc/hosts` y abrir `http://tickets.local` (DocumentRoot = carpeta del proyecto; el prefijo queda en raíz automáticamente).

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
├── tools/                 # montar.php, provision_runtime.ps1, instalador
├── index.php
├── .htaccess
└── composer.json
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
