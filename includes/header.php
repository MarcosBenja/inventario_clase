<?php
$_rol     = $_SESSION['usuario_rol'] ?? '';
$_nombre  = $_SESSION['usuario_nombre'] ?? '';
$_inicial = strtoupper(mb_substr($_nombre, 0, 1));
$_usuario = $_SESSION['usuario_usuario'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Control de Inventario — Marcos Benjamin Morazan Rivas">
    <title><?= htmlspecialchars($pageTitle ?? 'Sistema de Inventario') ?> | Inventario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>/modules/dashboard/index.php" class="sidebar-logo">
            <i class="bi bi-boxes"></i>
            <span>Inventario</span>
        </a>
    </div>

    <ul class="sidebar-nav">
        <li class="sidebar-section">Principal</li>
        <li>
            <a href="<?= BASE_URL ?>/modules/dashboard/index.php" class="sidebar-link <?= ($activeModule ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-section">Catálogo</li>
        <?php if ($_rol === 'admin'): ?>
        <li>
            <a href="<?= BASE_URL ?>/modules/categorias/index.php" class="sidebar-link <?= ($activeModule ?? '') === 'categorias' ? 'active' : '' ?>">
                <i class="bi bi-tag"></i><span>Categorías</span>
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="<?= BASE_URL ?>/modules/productos/index.php" class="sidebar-link <?= ($activeModule ?? '') === 'productos' ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i><span>Productos</span>
            </a>
        </li>

        <li class="sidebar-section">Movimientos</li>
        <li>
            <a href="<?= BASE_URL ?>/modules/entradas/crear.php" class="sidebar-link <?= ($activeModule ?? '') === 'entradas' ? 'active' : '' ?>">
                <i class="bi bi-arrow-down-circle"></i><span>Registrar Entrada</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/modules/salidas/crear.php" class="sidebar-link <?= ($activeModule ?? '') === 'salidas' ? 'active' : '' ?>">
                <i class="bi bi-arrow-up-circle"></i><span>Registrar Salida</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/modules/movimientos/index.php" class="sidebar-link <?= ($activeModule ?? '') === 'movimientos' ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i><span>Historial</span>
            </a>
        </li>

        <?php if ($_rol === 'admin'): ?>
        <li class="sidebar-section">Administración</li>
        <li>
            <a href="<?= BASE_URL ?>/modules/usuarios/index.php" class="sidebar-link <?= ($activeModule ?? '') === 'usuarios' ? 'active' : '' ?>">
                <i class="bi bi-people"></i><span>Usuarios</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="sidebar-avatar"><?= $_inicial ?></div>
        <div style="flex:1;min-width:0">
            <div class="sidebar-user-name"><?= htmlspecialchars($_nombre) ?></div>
            <div class="sidebar-user-role"><?= $_rol === 'admin' ? 'Administrador' : 'Operador' ?></div>
        </div>
        <a href="<?= BASE_URL ?>/modules/auth/logout.php" class="sidebar-logout" title="Cerrar sesión">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</aside>

<div class="main-wrapper">
    <nav class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list" style="font-size:1.1rem"></i>
        </button>
        <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></div>
        <div class="topbar-right">
            <span class="role-badge <?= $_rol === 'admin' ? 'role-admin' : 'role-operador' ?>">
                <i class="bi bi-<?= $_rol === 'admin' ? 'shield-check' : 'person' ?>"></i>
                <?= $_rol === 'admin' ? 'Administrador' : 'Operador' ?>
            </span>
        </div>
    </nav>
    <div class="content-area">
