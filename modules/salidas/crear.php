<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarSesion();

$pageTitle    = 'Registrar Salida';
$activeModule = 'salidas';

$productos = $pdo->query('SELECT p.*, c.nombre AS cat_nombre FROM productos p JOIN categorias c ON p.id_categoria = c.id WHERE p.estado = 1 ORDER BY p.nombre')->fetchAll();

$errores = [];
$datos   = ['id_producto' => '', 'fecha' => date('Y-m-d'), 'cantidad' => '', 'motivo' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['id_producto'] = (int)($_POST['id_producto'] ?? 0);
    $datos['fecha']       = $_POST['fecha'] ?? date('Y-m-d');
    $datos['cantidad']    = (int)($_POST['cantidad'] ?? 0);
    $datos['motivo']      = trim($_POST['motivo'] ?? '');

    if (!$datos['id_producto']) $errores[] = 'Seleccione un producto.';
    if ($datos['cantidad'] <= 0) $errores[] = 'La cantidad debe ser mayor a cero.';
    if ($datos['fecha'] === '')  $errores[] = 'La fecha es requerida.';
    if ($datos['motivo'] === '') $errores[] = 'El motivo es requerido.';

    if ($datos['id_producto'] && $datos['cantidad'] > 0) {
        $prod = $pdo->prepare('SELECT * FROM productos WHERE id = ? AND estado = 1');
        $prod->execute([$datos['id_producto']]);
        $producto = $prod->fetch();

        if (!$producto) {
            $errores[] = 'El producto seleccionado no es válido o está inactivo.';
        } elseif ($datos['cantidad'] > $producto['existencia_actual']) {
            $errores[] = 'Stock insuficiente. Disponible: ' . $producto['existencia_actual'] . ' unidades.';
        }
    }

    if (empty($errores)) {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO salidas (id_producto, fecha, cantidad, motivo, id_usuario) VALUES (?, ?, ?, ?, ?)')
            ->execute([$datos['id_producto'], $datos['fecha'], $datos['cantidad'], $datos['motivo'], $_SESSION['usuario_id']]);
        $pdo->prepare('UPDATE productos SET existencia_actual = existencia_actual - ? WHERE id = ?')
            ->execute([$datos['cantidad'], $datos['id_producto']]);
        $pdo->commit();
        header('Location: ' . BASE_URL . '/modules/salidas/index.php?msg=creado');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Registrar Salida</div>
        <div class="page-subtitle">Retiro de unidades del inventario</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/salidas/index.php" class="btn btn-outline-secondary">
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
                <form method="POST" id="form-salida">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="salida-producto">Producto *</label>
                            <select class="form-select" id="salida-producto" name="id_producto" required onchange="actualizarStockSalida(this)">
                                <option value="">Seleccione un producto activo...</option>
                                <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id'] ?>" data-stock="<?= $p['existencia_actual'] ?>"
                                    <?= $datos['id_producto'] == $p['id'] ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($p['codigo']) ?>] <?= htmlspecialchars($p['nombre']) ?> — Stock: <?= $p['existencia_actual'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="info-stock-salida" style="display:none" class="col-12">
                            <div id="alerta-stock" style="padding:.75rem 1rem;border-radius:8px;border:1px solid;font-size:.875rem">
                                <i class="bi bi-box-seam me-2"></i>
                                Stock disponible: <strong id="stock-disponible">—</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="salida-fecha">Fecha *</label>
                            <input type="date" class="form-control" id="salida-fecha" name="fecha" value="<?= $datos['fecha'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="salida-cantidad">Cantidad *</label>
                            <input type="number" class="form-control" id="salida-cantidad" name="cantidad" min="1" value="<?= $datos['cantidad'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Responsable</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="salida-motivo">Motivo *</label>
                            <input type="text" class="form-control" id="salida-motivo" name="motivo"
                                   placeholder="Ej: Venta, consumo interno, merma..."
                                   value="<?= htmlspecialchars($datos['motivo']) ?>" required>
                        </div>
                        <div class="col-12 pt-1">
                            <button type="submit" class="btn btn-danger" id="btn-registrar-salida">
                                <i class="bi bi-arrow-up-circle me-2"></i>Registrar Salida
                            </button>
                            <a href="<?= BASE_URL ?>/modules/salidas/index.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body" style="padding:1.5rem">
                <h6 style="font-weight:600;margin-bottom:1rem"><i class="bi bi-shield-exclamation me-2" style="color:#ef4444"></i>Validaciones</h6>
                <ul style="padding-left:1.125rem;font-size:.8438rem;color:var(--text-secondary);line-height:1.9">
                    <li>Solo productos <strong>activos</strong> pueden registrar salidas.</li>
                    <li>No se permiten salidas con <strong>stock insuficiente</strong>.</li>
                    <li>El sistema verifica el stock <strong>antes de registrar</strong>.</li>
                    <li>No se generarán <strong>existencias negativas</strong>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function actualizarStockSalida(sel) {
    const opt    = sel.options[sel.selectedIndex];
    const box    = document.getElementById('info-stock-salida');
    const alert  = document.getElementById('alerta-stock');
    const sdis   = document.getElementById('stock-disponible');
    if (sel.value) {
        const stock = parseInt(opt.dataset.stock, 10);
        box.style.display = 'block';
        sdis.textContent  = stock + ' unidades';
        if (stock === 0) {
            alert.style.background  = 'rgba(239,68,68,.06)';
            alert.style.borderColor = 'rgba(239,68,68,.2)';
            alert.style.color       = '#dc2626';
        } else if (stock <= 5) {
            alert.style.background  = 'rgba(245,158,11,.06)';
            alert.style.borderColor = 'rgba(245,158,11,.2)';
            alert.style.color       = '#d97706';
        } else {
            alert.style.background  = 'rgba(16,185,129,.06)';
            alert.style.borderColor = 'rgba(16,185,129,.2)';
            alert.style.color       = '#059669';
        }
    } else {
        box.style.display = 'none';
    }
}
window.addEventListener('load', function() {
    const sel = document.getElementById('salida-producto');
    if (sel && sel.value) actualizarStockSalida(sel);
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
