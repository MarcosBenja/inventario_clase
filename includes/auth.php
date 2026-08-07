<?php
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . '/modules/auth/login.php');
        exit;
    }
}

function verificarAdmin() {
    verificarSesion();
    if ($_SESSION['usuario_rol'] !== 'admin') {
        header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
        exit;
    }
}
