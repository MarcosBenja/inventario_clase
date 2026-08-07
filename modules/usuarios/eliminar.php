<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/modules/usuarios/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/modules/usuarios/index.php'); exit; }

if ($id === (int)$_SESSION['usuario_id']) {
    header('Location: ' . BASE_URL . '/modules/usuarios/index.php?msg=no_autoeliminarse');
    exit;
}

try {
    $pdo->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
    header('Location: ' . BASE_URL . '/modules/usuarios/index.php?msg=eliminado');
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . '/modules/usuarios/index.php?msg=error');
}
exit;
