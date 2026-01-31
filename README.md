# Sistema de Tickets - PHP 8.2

Sistema de gestión de tickets desarrollado con PHP 8.2, siguiendo la arquitectura MVC y utilizando clases entidades.

## Características

- ✅ Arquitectura MVC
- ✅ Clases entidades (Usuario, Ticket, Mensaje)
- ✅ Formulario público para creación de tickets (sin registro de clientes)
- ✅ Sistema de autenticación solo para soporte
- ✅ URLs amigables en formato `{URL}/controller/method/?parameter=`
- ✅ Sistema de tickets con estados (abierto, en_proceso, cerrado)
- ✅ Sistema de mensajes/respuestas a tickets
- ✅ Carga de archivos (imágenes/videos) con vista previa
- ✅ Validación de correo electrónico

## Requisitos

- PHP 8.2 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Composer (para autoloading)

## Instalación

### Instalación en Windows

#### Requisitos Previos

1. **Instalar XAMPP** (incluye Apache, MySQL y PHP 8.2):
   - Descargar desde: https://www.apachefriends.org/
   - Instalar en la ubicación predeterminada: `C:\xampp82\`

2. **Instalar Composer**:
   - Descargar el instalador desde: https://getcomposer.org/download/
   - Ejecutar `Composer-Setup.exe`
   - **Importante**: Durante la instalación, seleccionar la ruta de PHP de XAMPP:
     ```
     C:\xampp82\php\php.exe
     ```
   - Si ya tienes Composer instalado, verificar que apunte a la versión correcta de PHP

#### Pasos de Instalación

1. **Clonar o descargar el proyecto** en la carpeta `htdocs` de XAMPP:
   ```
   C:\xampp82\htdocs\proyectoWebTicket
   ```

2. **Abrir PowerShell o CMD** y navegar al directorio del proyecto:
   ```powershell
   cd C:\xampp82\htdocs\proyectoWebTicket
   ```

3. **Instalar dependencias de Composer**:
   
   **Opción A - Si Composer está configurado con PHP de XAMPP:**
   ```powershell
   composer install
   ```
   
   **Opción B - Si necesitas especificar la ruta de PHP manualmente:**
   ```powershell
   C:\xampp82\php\php.exe C:\Users\TuUsuario\AppData\Roaming\Composer\composer.phar install
   ```
   
   **Opción C - Usando el binario de Composer directamente:**
   ```powershell
   php C:\xampp82\php\composer.phar install
   ```
   
   **Nota**: Si tienes múltiples versiones de PHP instaladas, asegúrate de usar la de XAMPP.

4. **Crear el directorio de uploads**:
   ```powershell
   mkdir public\uploads
   ```

5. **Configurar la base de datos**:
   - Abre XAMPP Control Panel
   - Inicia los servicios Apache y MySQL
   - Abre phpMyAdmin (http://localhost/phpmyadmin)
   - Importa el archivo `database/schema.sql` o ejecuta:
   ```sql
   source C:/xampp82/htdocs/proyectoWebTicket/database/schema.sql
   ```

6. **Configurar la conexión a la base de datos**:
   - Edita el archivo `config/config.php` y ajusta los datos de conexión:
   ```php
   'database' => [
       'host' => 'localhost',
       'dbname' => 'ticket_system',
       'username' => 'root',
       'password' => '', // Dejar vacío por defecto en XAMPP
   ]
   ```

7. **Configurar la URL base** (si es necesario):
   - Edita `config/config.php` y ajusta la URL:
   ```php
   'url' => 'http://localhost/proyectoWebTicket',
   ```

8. **Verificar permisos del directorio de uploads**:
   - Asegúrate de que el directorio `public/uploads` tenga permisos de escritura

### Instalación en Linux

#### Requisitos Previos

1. **Instalar PHP 8.2 y extensiones necesarias**:
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl
   
   # CentOS/RHEL/Fedora
   sudo dnf install php8.2 php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl
   ```

2. **Instalar Apache y MySQL/MariaDB**:
   ```bash
   # Ubuntu/Debian
   sudo apt install apache2 mysql-server
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   
   # CentOS/RHEL/Fedora
   sudo dnf install httpd mariadb-server
   sudo systemctl enable httpd mariadb
   sudo systemctl start httpd mariadb
   ```

3. **Instalar Composer**:
   ```bash
   # Descargar e instalar Composer globalmente
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   sudo chmod +x /usr/local/bin/composer
   
   # Verificar instalación
   composer --version
   ```

#### Pasos de Instalación

1. **Clonar o descargar el proyecto** en el directorio web:
   ```bash
   # Opción A: En /var/www/html (Apache por defecto)
   sudo mkdir -p /var/www/html/proyectoWebTicket
   cd /var/www/html/proyectoWebTicket
   
   # Opción B: En tu directorio home y crear enlace simbólico
   cd ~
   mkdir proyectoWebTicket
   cd proyectoWebTicket
   ```

2. **Instalar dependencias de Composer**:
   ```bash
   composer install
   ```

3. **Crear el directorio de uploads**:
   ```bash
   mkdir -p public/uploads
   chmod 755 public/uploads
   ```

4. **Configurar permisos** (si instalaste en /var/www/html):
   ```bash
   sudo chown -R www-data:www-data /var/www/html/proyectoWebTicket
   sudo chmod -R 755 /var/www/html/proyectoWebTicket
   sudo chmod -R 775 /var/www/html/proyectoWebTicket/public/uploads
   ```

5. **Configurar la base de datos**:
   ```bash
   # Acceder a MySQL
   sudo mysql -u root -p
   
   # Dentro de MySQL, ejecutar:
   source /ruta/completa/al/proyecto/database/schema.sql
   # O importar desde phpMyAdmin
   ```

6. **Configurar la conexión a la base de datos**:
   - Edita el archivo `config/config.php`:
   ```php
   'database' => [
       'host' => 'localhost',
       'dbname' => 'ticket_system',
       'username' => 'root',
       'password' => 'tu_contraseña_mysql',
   ]
   ```

7. **Configurar Apache** (si es necesario):
   - Crear archivo de configuración virtual host:
   ```bash
   sudo nano /etc/apache2/sites-available/proyectoWebTicket.conf
   ```
   
   - Agregar configuración:
   ```apache
   <VirtualHost *:80>
       ServerName localhost
       DocumentRoot /var/www/html/proyectoWebTicket
       
       <Directory /var/www/html/proyectoWebTicket>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   
   - Habilitar el sitio:
   ```bash
   sudo a2ensite proyectoWebTicket.conf
   sudo systemctl reload apache2
   ```

8. **Configurar la URL base**:
   - Edita `config/config.php`:
   ```php
   'url' => 'http://localhost/proyectoWebTicket',
   # O si usas un dominio:
   'url' => 'http://tudominio.com',
   ```

### Verificación de la Instalación

Después de completar los pasos anteriores:

1. **Acceder a la aplicación**:
   - Windows: http://localhost/proyectoWebTicket
   - Linux: http://localhost/proyectoWebTicket (o tu dominio configurado)

2. **Verificar que el formulario de tickets funcione**:
   - Deberías ver el formulario público para crear tickets

3. **Probar el login de soporte**:
   - Ir a: http://localhost/proyectoWebTicket/login
   - Usar las credenciales: `soporte@demo.com` / `password`

## Usuarios de Prueba

El script de base de datos incluye un usuario de soporte de ejemplo:

- **Soporte**: 
  - Email: `soporte@demo.com`
  - Contraseña: `password`

**Nota:** Los clientes NO necesitan registrarse. Pueden crear tickets directamente desde el formulario público.

## Estructura del Proyecto

```
proyectoWebTicket/
├── app/
│   ├── Core/           # Clases core (Router, Request, Session, etc.)
│   ├── Controllers/    # Controladores
│   ├── Models/         # Modelos
│   ├── Entities/       # Entidades
│   └── Views/          # Vistas
├── config/             # Archivos de configuración
├── database/           # Scripts SQL
├── index.php           # Punto de entrada
├── .htaccess          # Configuración de URLs amigables
└── composer.json       # Dependencias
```

## URLs Amigables

El sistema utiliza URLs amigables en el formato:
- `/{controller}/{method}/?parameter=value`

Ejemplos:
- `/` o `/ticket/create` - Formulario público para crear tickets
- `/login` - Página de login (solo para soporte)
- `/soporte/dashboard` - Dashboard de soporte
- `/soporte/tickets/view?id=1` - Ver ticket específico

## Funcionalidades

### Formulario Público (Clientes):
- Crear tickets sin necesidad de registro
- Campos: Nombre, Apellido, Correo (con validación), Celular, ISP, Problema, Descripción
- Carga de archivos (imágenes/videos) con vista previa
- Validación de formato de correo electrónico

### Para Soporte:
- Ver todos los tickets con información completa del cliente
- Ver detalles completos: datos del cliente, problema, descripción, archivos adjuntos
- Responder a tickets
- Cambiar el estado de tickets (abierto, en_proceso, cerrado)
- Asignar tickets a sí mismo automáticamente al responder

## Desarrollo

El proyecto sigue las siguientes convenciones:

- **PSR-4 Autoloading**: Namespaces según estructura de carpetas
- **MVC**: Separación clara de responsabilidades
- **Entidades**: Clases que representan las tablas de la base de datos
- **Modelos**: Acceso a datos y lógica de negocio
- **Controladores**: Manejo de peticiones HTTP
- **Vistas**: Presentación de datos

## Licencia

MIT License
