<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarSesion();

$pageTitle   = 'Dashboard';
$activeModule = 'dashboard';

$totalCategorias    = $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
$totalProductos     = $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
$productosActivos   = $pdo->query('SELECT COUNT(*) FROM productos WHERE estado = 1')->fetchColumn();
$productosInactivos = $pdo->query('SELECT COUNT(*) FROM productos WHERE estado = 0')->fetchColumn();
$productosAgotados  = $pdo->query('SELECT COUNT(*) FROM productos WHERE existencia_actual = 0 AND estado = 1')->fetchColumn();
$productosBajos     = $pdo->query('SELECT COUNT(*) FROM productos WHERE existencia_actual > 0 AND existencia_actual <= existencia_minima AND estado = 1')->fetchColumn();
$totalEntradas      = $pdo->query('SELECT COUNT(*) FROM entradas')->fetchColumn();
$totalSalidas       = $pdo->query('SELECT COUNT(*) FROM salidas')->fetchColumn();

$productosRecientes = $pdo->query('
    SELECT p.*, c.nombre AS cat FROM productos p
    JOIN categorias c ON p.id_categoria = c.id
    ORDER BY p.created_at DESC LIMIT 6
')->fetchAll();

$movimientosRecientes = $pdo->query("
    SELECT 'entrada' AS tipo, e.fecha, e.cantidad, e.motivo, p.nombre AS producto
    FROM entradas e
    JOIN productos p ON e.id_producto = p.id
    UNION ALL
    SELECT 'salida' AS tipo, s.fecha, s.cantidad, s.motivo, p.nombre AS producto
    FROM salidas s
    JOIN productos p ON s.id_producto = p.id
    ORDER BY fecha DESC LIMIT 8
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Panel Principal</div>
        <div class="page-subtitle">Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> &mdash; <?= date('d/m/Y') ?></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-tag"></i></div>
            <div><div class="stat-value"><?= $totalCategorias ?></div><div class="stat-label">Categorías</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-box-seam"></i></div>
            <div><div class="stat-value"><?= $totalProductos ?></div><div class="stat-label">Total Productos</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-check-circle"></i></div>
            <div><div class="stat-value"><?= $productosActivos ?></div><div class="stat-label">Activos</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon gray"><i class="bi bi-dash-circle"></i></div>
            <div><div class="stat-value"><?= $productosInactivos ?></div><div class="stat-label">Inactivos</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-exclamation-circle"></i></div>
            <div><div class="stat-value"><?= $productosAgotados ?></div><div class="stat-label">Agotados</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="stat-value"><?= $productosBajos ?></div><div class="stat-label">Stock Bajo</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon teal"><i class="bi bi-arrow-down-circle"></i></div>
            <div><div class="stat-value"><?= $totalEntradas ?></div><div class="stat-label">Entradas</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-arrow-up-circle"></i></div>
            <div><div class="stat-value"><?= $totalSalidas ?></div><div class="stat-label">Salidas</div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:transparent;border-bottom:1px solid var(--border-color);padding:1.125rem 1.5rem">
                <h6 class="mb-0" style="font-weight:600">Movimientos Recientes</h6>
                <a href="<?= BASE_URL ?>/modules/movimientos/index.php" class="btn btn-sm btn-outline-primary">Ver todo</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientosRecientes)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Sin movimientos registrados.</td></tr>
                            <?php else: ?>
                            <?php foreach ($movimientosRecientes as $m): ?>
                            <tr>
                                <td>
                                    <span class="bstatus b-<?= $m['tipo'] ?>">
                                        <i class="bi bi-arrow-<?= $m['tipo'] === 'entrada' ? 'down' : 'up' ?>-short"></i>
                                        <?= ucfirst($m['tipo']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($m['producto']) ?></td>
                                <td><strong><?= $m['cantidad'] ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
                                <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($m['motivo']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:transparent;border-bottom:1px solid var(--border-color);padding:1.125rem 1.5rem">
                <h6 class="mb-0" style="font-weight:600">Estado de Productos</h6>
                <a href="<?= BASE_URL ?>/modules/productos/index.php" class="btn btn-sm btn-outline-primary">Ver todo</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Producto</th><th>Stock</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productosRecientes as $p): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:.8125rem"><?= htmlspecialchars($p['nombre']) ?></div>
                                    <div style="font-size:.75rem;color:var(--text-secondary)"><?= htmlspecialchars($p['cat']) ?></div>
                                </td>
                                <td><strong><?= $p['existencia_actual'] ?></strong></td>
                                <td>
                                    <?php if ($p['existencia_actual'] == 0): ?>
                                    <span class="bstatus b-agotado">Agotado</span>
                                    <?php elseif ($p['existencia_actual'] <= $p['existencia_minima']): ?>
                                    <span class="bstatus b-bajo">Stock bajo</span>
                                    <?php else: ?>
                                    <span class="bstatus b-normal">Normal</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
