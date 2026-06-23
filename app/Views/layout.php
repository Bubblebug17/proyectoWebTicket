<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'SoporteTicket') ?></title>
    <link rel="stylesheet" href="/proyectoWebTicket/public/css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="<?= isset($user) ? '/proyectoWebTicket/soporte/dashboard' : '/proyectoWebTicket' ?>" class="brand">
                <span class="brand__icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </span>
                SoporteTicket
            </a>
            <ul class="site-nav">
                <?php if (isset($user)): ?>
                    <li><a href="/proyectoWebTicket/soporte/dashboard">Dashboard</a></li>
                    <li><a href="/proyectoWebTicket/soporte/tickets">Tickets</a></li>
                    <li><a href="/proyectoWebTicket/soporte/reportes">Reportes</a></li>
                    <li>
                        <a href="/proyectoWebTicket/logout">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Cerrar Sesión
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="/proyectoWebTicket">Crear Ticket</a></li>
                    <li><a href="/proyectoWebTicket/login">Iniciar Sesión</a></li>
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
