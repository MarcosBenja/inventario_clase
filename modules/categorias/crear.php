<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

$pageTitle    = 'Nueva Categoría';
$activeModule = 'categorias';

$errores = [];
$datos   = ['nombre' => '', 'descripcion' => '', 'estado' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre']      = trim($_POST['nombre'] ?? '');
    $datos['descripcion'] = trim($_POST['descripcion'] ?? '');
    $datos['estado']      = isset($_POST['estado']) ? 1 : 0;

    if ($datos['nombre'] === '') $errores[] = 'El nombre es requerido.';

    if (empty($errores)) {
        $stmt = $pdo->prepare('INSERT INTO categorias (nombre, descripcion, estado) VALUES (?, ?, ?)');
        $stmt->execute([$datos['nombre'], $datos['descripcion'], $datos['estado']]);
        header('Location: ' . BASE_URL . '/modules/categorias/index.php?msg=creado');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Nueva Categoría</div>
        <div class="page-subtitle">Complete los datos del formulario</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/categorias/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<?php if (!empty($errores)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= implode('<br>', array_map('htmlspecialchars', $errores)) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:1.75rem">
        <form method="POST" id="form-crear-categoria">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="cat-nombre">Nombre *</label>
                    <input type="text" class="form-control" id="cat-nombre" name="nombre"
                           value="<?= htmlspecialchars($datos['nombre']) ?>"
                           placeholder="Nombre de la categoría" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="estado" id="cat-estado" <?= $datos['estado'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="cat-estado">Activo</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="cat-descripcion">Descripción</label>
                    <textarea class="form-control" id="cat-descripcion" name="descripcion" rows="3"
                              placeholder="Descripción opcional..."><?= htmlspecialchars($datos['descripcion']) ?></textarea>
                </div>
                <div class="col-12 pt-1">
                    <button type="submit" class="btn btn-primary" id="btn-guardar-categoria">
                        <i class="bi bi-check-lg me-2"></i>Guardar Categoría
                    </button>
                    <a href="<?= BASE_URL ?>/modules/categorias/index.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
