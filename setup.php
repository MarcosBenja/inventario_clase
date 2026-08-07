<?php
$host    = 'localhost';
$dbname  = 'inventario_db';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $hash_admin = password_hash('1234', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios (nombre_completo, usuario, email, password, rol, estado) VALUES (?, ?, ?, ?, 'admin', 1)");
    $stmt->execute(['Marcos Benjamin Morazan Rivas', 'MARCOS', 'marcos@inventario.com', $hash_admin]);

    $hash_op = password_hash('1234', PASSWORD_DEFAULT);
    $stmt2 = $pdo->prepare("INSERT IGNORE INTO usuarios (nombre_completo, usuario, email, password, rol, estado) VALUES (?, ?, ?, ?, 'operador', 1)");
    $stmt2->execute(['Operador Sistema', 'OPERADOR', 'operador@inventario.com', $hash_op]);

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Setup</title>
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f1f5f9;margin:0}
    .box{background:#fff;padding:2rem 2.5rem;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.1);max-width:480px;width:100%}
    h2{color:#059669;margin-bottom:1rem}table{width:100%;border-collapse:collapse;margin:1rem 0}
    td,th{padding:.5rem .75rem;text-align:left;border:1px solid #e2e8f0;font-size:.875rem}
    th{background:#f8fafc;font-weight:600}a{color:#6366f1;font-weight:600}.warn{color:#dc2626;margin-top:1rem;font-size:.875rem}
    </style></head><body><div class="box">
    <h2>✅ Instalación completada</h2>
    <p>Usuarios creados exitosamente:</p>
    <table><tr><th>Usuario</th><th>Contraseña</th><th>Rol</th></tr>
    <tr><td>MARCOS</td><td>1234</td><td>Administrador</td></tr>
    <tr><td>OPERADOR</td><td>1234</td><td>Operador</td></tr></table>
    <a href="http://localhost/inventario/modules/auth/login.php">→ Ir al Login</a>
    <p class="warn">⚠️ <strong>Elimina este archivo (setup.php) por seguridad después de usarlo.</strong></p>
    </div></body></html>';

} catch (PDOException $e) {
    echo '<div style="font-family:sans-serif;padding:2rem;color:#dc2626"><strong>Error:</strong> ' . $e->getMessage() . '</div>';
}
