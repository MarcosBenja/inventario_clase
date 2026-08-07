<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarSesion();

$pageTitle    = 'Registrar Entrada';
$activeModule = 'entradas';

$productos = $pdo->query('SELECT p.*, c.nombre AS cat_nombre FROM productos p JOIN categorias c ON p.id_categoria = c.id WHERE p.estado = 1 ORDER BY p.nombre')->fetchAll();

$errores = [];
$datos   = ['id_producto' => '', 'fecha' => date('Y-m-d'), 'cantidad' => '', 'motivo' => ''];
$msg_ok  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['id_producto'] = (int)($_POST['id_producto'] ?? 0);
    $datos['fecha']       = $_POST['fecha'] ?? date('Y-m-d');
    $datos['cantidad']    = (int)($_POST['cantidad'] ?? 0);
    $datos['motivo']      = trim($_POST['motivo'] ?? '');

    if (!$datos['id_producto']) $errores[] = 'Seleccione un producto.';
    if ($datos['cantidad'] <= 0) $errores[] = 'La cantidad debe ser mayor a cero.';
    if ($datos['fecha'] === '')  $errores[] = 'La fecha es requerida.';
    if ($datos['motivo'] === '') $errores[] = 'El motivo es requerido.';

    if ($datos['id_producto']) {
        $chk = $pdo->prepare('SELECT id FROM productos WHERE id = ? AND estado = 1');
        $chk->execute([$datos['id_producto']]);
        if (!$chk->fetch()) $errores[] = 'El producto seleccionado no es válido o está inactivo.';
    }

    if (empty($errores)) {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO entradas (id_producto, fecha, cantidad, motivo, id_usuario) VALUES (?, ?, ?, ?, ?)')
            ->execute([$datos['id_producto'], $datos['fecha'], $datos['cantidad'], $datos['motivo'], $_SESSION['usuario_id']]);
        $pdo->prepare('UPDATE productos SET existencia_actual = existencia_actual + ? WHERE id = ?')
            ->execute([$datos['cantidad'], $datos['id_producto']]);
        $pdo->commit();
        header('Location: ' . BASE_URL . '/modules/entradas/index.php?msg=creado');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Registrar Entrada</div>
        <div class="page-subtitle">Ingreso de unidades al inventario</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/entradas/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-list-ul me-2"></i>Ver Historial
    </a>
</div>

<?php if (!empty($errores)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= implode('<br>', array_map('htmlspecialchars', $errores)) ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body" style="padding:1.75rem">
                <form method="POST" id="form-entrada">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="entrada-producto">Producto *</label>
                            <select class="form-select" id="entrada-producto" name="id_producto" required onchange="actualizarStock(this)">
                                <option value="">Seleccione un producto activo...</option>
                                <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id'] ?>" data-stock="<?= $p['existencia_actual'] ?>" data-min="<?= $p['existencia_minima'] ?>"
                                    <?= $datos['id_producto'] == $p['id'] ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($p['codigo']) ?>] <?= htmlspecialchars($p['nombre']) ?> — Stock: <?= $p['existencia_actual'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="info-stock" style="display:none" class="col-12">
                            <div style="padding:.75rem 1rem;background:rgba(59,130,246,.06);border-radius:8px;border:1px solid rgba(59,130,246,.15);font-size:.875rem">
                                <i class="bi bi-info-circle me-2" style="color:#3b82f6"></i>
                                Stock actual: <strong id="stock-valor">—</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="entrada-fecha">Fecha *</label>
                            <input type="date" class="form-control" id="entrada-fecha" name="fecha" value="<?= $datos['fecha'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="entrada-cantidad">Cantidad *</label>
                            <input type="number" class="form-control" id="entrada-cantidad" name="cantidad" min="1" value="<?= $datos['cantidad'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Responsable</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="entrada-motivo">Motivo *</label>
                            <input type="text" class="form-control" id="entrada-motivo" name="motivo"
                                   placeholder="Ej: Compra a proveedor, devolución, ajuste de inventario..."
                                   value="<?= htmlspecialchars($datos['motivo']) ?>" required>
                        </div>
                        <div class="col-12 pt-1">
                            <button type="submit" class="btn btn-primary" id="btn-registrar-entrada">
                                <i class="bi bi-arrow-down-circle me-2"></i>Registrar Entrada
                            </button>
                            <a href="<?= BASE_URL ?>/modules/entradas/index.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body" style="padding:1.5rem">
                <h6 style="font-weight:600;margin-bottom:1rem"><i class="bi bi-info-circle me-2" style="color:#6366f1"></i>Información</h6>
                <ul style="padding-left:1.125rem;font-size:.8438rem;color:var(--text-secondary);line-height:1.9">
                    <li>Solo productos <strong>activos</strong> pueden recibir entradas.</li>
                    <li>La cantidad debe ser <strong>mayor a cero</strong>.</li>
                    <li>El stock se actualiza <strong>automáticamente</strong>.</li>
                    <li>El usuario responsable se toma de la sesión actual.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function actualizarStock(sel) {
    const opt = sel.options[sel.selectedIndex];
    const infoBox = document.getElementById('info-stock');
    const stockVal = document.getElementById('stock-valor');
    if (sel.value) {
        infoBox.style.display = 'block';
        stockVal.textContent = opt.dataset.stock + ' unidades';
    } else {
        infoBox.style.display = 'none';
    }
}
window.addEventListener('load', function() {
    const sel = document.getElementById('entrada-producto');
    if (sel && sel.value) actualizarStock(sel);
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
