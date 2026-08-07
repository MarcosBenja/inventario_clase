<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

$pageTitle    = 'Editar Categoría';
$activeModule = 'categorias';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/modules/categorias/index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM categorias WHERE id = ?');
$stmt->execute([$id]);
$cat = $stmt->fetch();
if (!$cat) { header('Location: ' . BASE_URL . '/modules/categorias/index.php'); exit; }

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado      = isset($_POST['estado']) ? 1 : 0;

    if ($nombre === '') $errores[] = 'El nombre es requerido.';

    if (empty($errores)) {
        $pdo->prepare('UPDATE categorias SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?')
            ->execute([$nombre, $descripcion, $estado, $id]);
        header('Location: ' . BASE_URL . '/modules/categorias/index.php?msg=editado');
        exit;
    }
    $cat['nombre']      = $nombre;
    $cat['descripcion'] = $descripcion;
    $cat['estado']      = $estado;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Editar Categoría</div>
        <div class="page-subtitle">ID #<?= $id ?></div>
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
        <form method="POST" id="form-editar-categoria">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="cat-nombre">Nombre *</label>
                    <input type="text" class="form-control" id="cat-nombre" name="nombre"
                           value="<?= htmlspecialchars($cat['nombre']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="estado" id="cat-estado" <?= $cat['estado'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="cat-estado">Activo</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="cat-descripcion">Descripción</label>
                    <textarea class="form-control" id="cat-descripcion" name="descripcion" rows="3"><?= htmlspecialchars($cat['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="col-12 pt-1">
                    <button type="submit" class="btn btn-primary" id="btn-actualizar-categoria">
                        <i class="bi bi-check-lg me-2"></i>Actualizar Categoría
                    </button>
                    <a href="<?= BASE_URL ?>/modules/categorias/index.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
