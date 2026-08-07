<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarSesion();

$pageTitle    = 'Historial de Movimientos';
$activeModule = 'movimientos';

$buscar      = trim($_GET['buscar'] ?? '');
$tipo        = $_GET['tipo'] ?? '';
$id_producto = $_GET['producto'] ?? '';
$id_usuario  = $_GET['usuario'] ?? '';
$fecha_desde = $_GET['desde'] ?? '';
$fecha_hasta = $_GET['hasta'] ?? '';

$partes  = [];
$params  = [];

$sqlE = "SELECT 'Entrada' AS tipo, e.id, e.fecha, e.cantidad, e.motivo, p.nombre AS prod_nombre, p.codigo, u.nombre_completo AS usuario_nombre
         FROM entradas e
         JOIN productos p ON e.id_producto = p.id
         JOIN usuarios u ON e.id_usuario = u.id
         WHERE 1=1";

$sqlS = "SELECT 'Salida' AS tipo, s.id, s.fecha, s.cantidad, s.motivo, p.nombre AS prod_nombre, p.codigo, u.nombre_completo AS usuario_nombre
         FROM salidas s
         JOIN productos p ON s.id_producto = p.id
         JOIN usuarios u ON s.id_usuario = u.id
         WHERE 1=1";

$paramsE = [];
$paramsS = [];

if ($buscar !== '') {
    $like = "%$buscar%";
    $sqlE .= ' AND (p.nombre LIKE ? OR p.codigo LIKE ? OR e.motivo LIKE ?)';
    $paramsE[] = $like; $paramsE[] = $like; $paramsE[] = $like;
    $sqlS .= ' AND (p.nombre LIKE ? OR p.codigo LIKE ? OR s.motivo LIKE ?)';
    $paramsS[] = $like; $paramsS[] = $like; $paramsS[] = $like;
}
if ($id_producto !== '') {
    $sqlE .= ' AND e.id_producto = ?'; $paramsE[] = $id_producto;
    $sqlS .= ' AND s.id_producto = ?'; $paramsS[] = $id_producto;
}
if ($id_usuario !== '') {
    $sqlE .= ' AND e.id_usuario = ?'; $paramsE[] = $id_usuario;
    $sqlS .= ' AND s.id_usuario = ?'; $paramsS[] = $id_usuario;
}
if ($fecha_desde !== '') {
    $sqlE .= ' AND e.fecha >= ?'; $paramsE[] = $fecha_desde;
    $sqlS .= ' AND s.fecha >= ?'; $paramsS[] = $fecha_desde;
}
if ($fecha_hasta !== '') {
    $sqlE .= ' AND e.fecha <= ?'; $paramsE[] = $fecha_hasta;
    $sqlS .= ' AND s.fecha <= ?'; $paramsS[] = $fecha_hasta;
}

$movimientos = [];

if ($tipo !== 'Salida') {
    $stmtE = $pdo->prepare($sqlE);
    $stmtE->execute($paramsE);
    $movimientos = array_merge($movimientos, $stmtE->fetchAll());
}
if ($tipo !== 'Entrada') {
    $stmtS = $pdo->prepare($sqlS);
    $stmtS->execute($paramsS);
    $movimientos = array_merge($movimientos, $stmtS->fetchAll());
}

usort($movimientos, function($a, $b) {
    return strcmp($b['fecha'] . $b['id'], $a['fecha'] . $a['id']);
});

$productos = $pdo->query('SELECT id, codigo, nombre FROM productos ORDER BY nombre')->fetchAll();
$usuarios  = $pdo->query('SELECT id, nombre_completo FROM usuarios ORDER BY nombre_completo')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Historial de Movimientos</div>
        <div class="page-subtitle"><?= count($movimientos) ?> registros totales</div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/modules/entradas/crear.php" class="btn btn-outline-success btn-sm">
            <i class="bi bi-arrow-down-circle me-1"></i>Entrada
        </a>
        <a href="<?= BASE_URL ?>/modules/salidas/crear.php" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-arrow-up-circle me-1"></i>Salida
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:1.25rem">
        <form method="GET" class="row g-2 mb-3" id="form-filtro-movimientos">
            <div class="col-md-3">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar producto o motivo..." value="<?= htmlspecialchars($buscar) ?>">
            </div>
            <div class="col-md-2">
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="Entrada" <?= $tipo === 'Entrada' ? 'selected' : '' ?>>Entradas</option>
                    <option value="Salida"  <?= $tipo === 'Salida'  ? 'selected' : '' ?>>Salidas</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="producto" class="form-select">
                    <option value="">Todos los productos</option>
                    <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $id_producto == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <input type="date" name="desde" class="form-control" value="<?= $fecha_desde ?>">
            </div>
            <div class="col-md-1">
                <input type="date" name="hasta" class="form-control" value="<?= $fecha_hasta ?>">
            </div>
            <div class="col-md-2">
                <select name="usuario" class="form-select">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $id_usuario == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['nombre_completo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
                <a href="<?= BASE_URL ?>/modules/movimientos/index.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table" id="tabla-movimientos">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Producto</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimientos)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron movimientos.</td></tr>
                    <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td>
                            <?php if ($m['tipo'] === 'Entrada'): ?>
                            <span class="bstatus b-entrada"><i class="bi bi-arrow-down-short"></i> Entrada</span>
                            <?php else: ?>
                            <span class="bstatus b-salida"><i class="bi bi-arrow-up-short"></i> Salida</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($m['prod_nombre']) ?></strong>
                            <div style="font-size:.75rem;color:var(--text-secondary)"><?= htmlspecialchars($m['codigo']) ?></div>
                        </td>
                        <td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
                        <td>
                            <strong style="color:<?= $m['tipo'] === 'Entrada' ? '#10b981' : '#ef4444' ?>">
                                <?= $m['tipo'] === 'Entrada' ? '+' : '-' ?><?= $m['cantidad'] ?>
                            </strong>
                        </td>
                        <td><?= htmlspecialchars($m['motivo']) ?></td>
                        <td><?= htmlspecialchars($m['usuario_nombre']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
