# Guía de Instalación - Sistema de Tickets

## Requisitos Previos

- PHP 8.2 o superior
- MySQL 5.7 o superior (o MariaDB equivalente)
- Apache con mod_rewrite habilitado
- Composer instalado globalmente

## Pasos de Instalación

### 1. Verificar Composer

Asegúrate de tener Composer instalado. Si no lo tienes, descárgalo desde [getcomposer.org](https://getcomposer.org/)

### 2. Instalar Dependencias

Abre una terminal en la carpeta del proyecto y ejecuta:

```bash
composer install
```

Esto creará la carpeta `vendor/` con el autoloader de PSR-4.

### 3. Configurar la Base de Datos

1. Abre phpMyAdmin o tu cliente MySQL preferido
2. Crea una nueva base de datos llamada `ticket_system` (o importa el script SQL)
3. Importa el archivo `database/schema.sql`:
   - En phpMyAdmin: Selecciona la base de datos → Pestaña "Importar" → Selecciona `database/schema.sql`
   - O ejecuta desde la terminal:
     ```bash
     mysql -u root -p ticket_system < database/schema.sql
     ```

### 4. Configurar la Conexión

Edita el archivo `config/config.php` y ajusta los datos de conexión:

```php
'database' => [
    'host' => 'localhost',
    'dbname' => 'ticket_system',
    'username' => 'root',        // Tu usuario de MySQL
    'password' => '',            // Tu contraseña de MySQL
    'charset' => 'utf8mb4'
],
```

### 5. Verificar mod_rewrite

Asegúrate de que mod_rewrite esté habilitado en Apache. En XAMPP generalmente viene habilitado por defecto.

Para verificar, busca en `httpd.conf`:
```
LoadModule rewrite_module modules/mod_rewrite.so
```

### 6. Acceder a la Aplicación

Abre tu navegador y visita:
```
http://localhost/proyectoWebTicket
```

## Usuarios de Prueba

El script SQL incluye dos usuarios de ejemplo:

**Cliente:**
- Email: `cliente@demo.com`
- Contraseña: `password`

**Soporte:**
- Email: `soporte@demo.com`
- Contraseña: `password`

## Solución de Problemas

### Error: "Class not found"
- Ejecuta `composer install` o `composer dump-autoload`

### Error: "404 - Página no encontrada"
- Verifica que mod_rewrite esté habilitado
- Verifica que el archivo `.htaccess` esté en la raíz del proyecto
- Verifica que la configuración de Apache permita `.htaccess`

### Error de conexión a la base de datos
- Verifica que MySQL esté corriendo
- Verifica los datos de conexión en `config/config.php`
- Verifica que la base de datos `ticket_system` exista

### Las URLs no funcionan
- Verifica que `RewriteBase` en `.htaccess` coincida con tu ruta del proyecto
- Si tu proyecto está en una subcarpeta diferente, ajusta `RewriteBase` y el `basePath` en `app/Core/Request.php`

## Estructura de URLs

El sistema utiliza URLs amigables en el formato:
- `/{controller}/{method}/?parameter=value`

Ejemplos:
- `/login` - Página de login
- `/cliente/dashboard` - Dashboard del cliente
- `/soporte/tickets/view?id=1` - Ver ticket #1
