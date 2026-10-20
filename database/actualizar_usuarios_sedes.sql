-- Alinea usuarios demo con el lineamiento de sedes (San Isidro, Miraflores, Lurín).
-- Ejecutar una vez sobre la base remota ya migrada (sucursales TB-SAN, TB-MIR, TB-LUR).
-- No borra tickets ni mensajes.

UPDATE usuarios u
INNER JOIN sucursales s ON s.codigo = 'TB-MIR'
SET u.sucursal_id = s.id
WHERE u.email IN ('tienda@demo.com', 'tienda@tambo.pe');

UPDATE usuarios u
INNER JOIN sucursales s ON s.codigo = 'TB-LUR'
SET u.sucursal_id = s.id
WHERE u.email IN ('almacen@demo.com', 'almacen@tambo.pe', 'area@demo.com', 'area@tambo.pe', 'logistica@demo.com', 'logistica@tambo.pe');

UPDATE usuarios u
INNER JOIN sucursales s ON s.codigo = 'TB-SAN'
SET u.sucursal_id = s.id
WHERE u.email IN (
    'soporte@demo.com',
    'soporte@tambo.pe',
    'tecnologia@demo.com',
    'tecnologia@tambo.pe',
    'administracion@demo.com',
    'administracion@tambo.pe',
    'otros@demo.com',
    'otros@tambo.pe'
);

UPDATE usuarios u
INNER JOIN tipos_ticket t ON t.codigo = 'logistico'
SET u.area_id = t.id
WHERE u.tipo = 'responsable_area'
  AND u.email IN ('logistica@demo.com', 'logistica@tambo.pe');

UPDATE usuarios u
INNER JOIN tipos_ticket t ON t.codigo = 'logistico'
SET u.area_id = t.id
WHERE u.tipo = 'responsable_area'
  AND u.email IN ('area@demo.com', 'area@tambo.pe')
  AND NOT EXISTS (
      SELECT 1
      FROM usuarios z
      INNER JOIN tipos_ticket tt ON tt.id = z.area_id AND tt.codigo = 'logistico'
      WHERE z.email IN ('logistica@demo.com', 'logistica@tambo.pe')
  );

-- Responsable de almacén en Lurín (si aún no existe)
INSERT INTO usuarios (nombre, apellido, email, celular, password, tipo, sucursal_id)
SELECT 'Pedro', 'Castillo', 'almacen@demo.com', '999111333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable_tienda', s.id
FROM sucursales s
WHERE s.codigo = 'TB-LUR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'almacen@demo.com');
