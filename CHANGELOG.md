# Changelog

## [2.0.0] - 2024

### Cambios Principales

#### Sistema de Tickets Público
- **Eliminado:** Sistema de registro y login para clientes
- **Agregado:** Formulario público para creación de tickets sin registro
- Los clientes ya no necesitan crear una cuenta para enviar tickets

#### Nuevos Campos en Tickets
- Nombre del cliente
- Apellido del cliente
- Correo electrónico (con validación de formato)
- Celular
- ISP (Claro, Win, Movistar, Bitel, Fibratel)
- Problema (resumen breve)
- Descripción detallada
- Archivo adjunto (imagen/video)

#### Sistema de Archivos
- Carga de archivos (imágenes y videos)
- Vista previa de archivos antes de enviar
- Visualización de archivos en el panel de soporte
- Validación de tipos de archivo y tamaño máximo (10MB)

#### Base de Datos
- Actualizada tabla `tickets` con nuevos campos
- Eliminada relación con tabla `usuarios` para clientes
- Tabla `usuarios` ahora solo para personal de soporte

#### Vistas Actualizadas
- Formulario público de tickets con validación en tiempo real
- Vista de éxito después de enviar ticket
- Panel de soporte actualizado con nueva información
- Visualización mejorada de tickets con todos los datos del cliente

### Archivos Nuevos
- `app/Controllers/TicketController.php` - Controlador para formulario público
- `app/Core/FileUpload.php` - Sistema de carga de archivos
- `app/Views/ticket/create.php` - Formulario público
- `app/Views/ticket/success.php` - Página de confirmación
- `database/migration_update.sql` - Script de migración para bases existentes

### Archivos Modificados
- `database/schema.sql` - Esquema actualizado
- `app/Entities/Ticket.php` - Nueva estructura de entidad
- `app/Models/TicketModel.php` - Consultas actualizadas
- `app/Controllers/AuthController.php` - Solo login para soporte
- `app/Controllers/SoporteController.php` - Adaptado a nueva estructura
- `app/Core/Application.php` - Rutas actualizadas
- Todas las vistas de soporte actualizadas

### Archivos Eliminados
- Funcionalidades de cliente (login, dashboard, etc.)
- Vistas de cliente (mantenidas por compatibilidad pero no accesibles)

## [1.0.0] - Versión Inicial

- Sistema básico de tickets
- Autenticación para clientes y soporte
- Dashboard para ambos tipos de usuarios
- Sistema de mensajes
