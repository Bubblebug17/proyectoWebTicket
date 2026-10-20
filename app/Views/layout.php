<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Intranet Tambo') ?></title>
    <link rel="icon" type="image/png" href="/proyectoWebTicket/public/img/tambo-sello.png">
    <link rel="stylesheet" href="/proyectoWebTicket/public/css/app.css?v=tambo-logo">
</head>
<body>
    <?php
    if (!isset($user) && !empty($_SESSION['user']) && is_array($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }
    $perfil = $user['tipo'] ?? '';
    $inicioIntranet = $perfil !== ''
        ? \App\Entities\Usuario::rutaInicio($perfil)
        : '/proyectoWebTicket/login';
    ?>
    <header class="site-header">
        <div class="container">
            <a href="<?= htmlspecialchars($inicioIntranet) ?>" class="brand">
                <img class="brand__mark" src="/proyectoWebTicket/public/img/tambo-sello.png" alt="" width="42" height="42">
                Intranet
            </a>
            <ul class="site-nav">
                <?php if ($perfil !== ''): ?>
                    <li><a href="/proyectoWebTicket/ticket/create">Nuevo ticket</a></li>
                    <?php if ($perfil === 'soporte'): ?>
                        <li><a href="/proyectoWebTicket/soporte/dashboard">Dashboard</a></li>
                        <li><a href="/proyectoWebTicket/soporte/tickets">Tickets</a></li>
                        <li><a href="/proyectoWebTicket/soporte/reportes">Reportes</a></li>
                    <?php elseif ($perfil === 'responsable_area'): ?>
                        <li><a href="/proyectoWebTicket/soporte/tickets">Mis tickets</a></li>
                    <?php endif; ?>
                    <li class="nav-user">
                        <?= htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? ''))) ?>
                        <span><?= htmlspecialchars(trim(($user['perfil'] ?? \App\Entities\Usuario::etiquetaPerfil($perfil)) . (!empty($user['area_nombre']) ? ' · ' . $user['area_nombre'] : ''))) ?></span>
                    </li>
                    <li>
                        <a href="/proyectoWebTicket/logout">Cerrar sesión</a>
                    </li>
                <?php else: ?>
                    <li><a href="/proyectoWebTicket/login">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <main class="page-content">
        <div class="container<?= ($narrowLayout ?? false) ? ' container--narrow' : '' ?>">
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?= $content ?? '' ?>
        </div>
    </main>
</body>
</html>
