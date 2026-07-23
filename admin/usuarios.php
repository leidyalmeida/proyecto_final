<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/conexion.php';

$usuarios = [];
$error_mensaje = "";

try {
    // Consulta exacta adaptada a la estructura de tu base de datos ecommerce_db
    $sql = "SELECT u.id, CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo, u.email, r.nombre_rol AS rol_real 
            FROM usuarios u 
            LEFT JOIN roles r ON u.rol_id = r.id 
            ORDER BY u.id ASC";
            
    $stmt = $conexion->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_mensaje = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fas fa-cogs"></i> Admin Panel</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Admin'); ?> (ADMIN)</span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>
    </nav>

    <div class="bg-white border-bottom shadow-sm mb-4">
        <div class="container py-2">
            <a href="dashboard.php" class="btn btn-outline-primary btn-sm me-2"><i class="fas fa-home"></i> Dashboard</a>
            <a href="categorias.php" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-tags"></i> Gestionar Categorías</a>
            <a href="productos.php" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-box"></i> Gestionar Productos</a>
            <a href="usuarios.php" class="btn btn-info btn-sm text-white me-2"><i class="fas fa-users"></i> Gestionar Usuarios</a>
            <a href="../index.php" class="btn btn-outline-dark btn-sm"><i class="fas fa-globe"></i> Ver Sitio Web</a>
        </div>
    </div>

    <div class="container mb-5">
        <h2 class="fw-bold text-secondary mb-4"><i class="fas fa-users text-info"></i> Lista de Usuarios Registrados</h2>

        <?php if (!empty($error_mensaje)): ?>
            <div class="alert alert-danger shadow-sm">
                <strong>Error de Base de Datos:</strong> <?= htmlspecialchars($error_mensaje); ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 ps-4"># ID</th>
                                <th class="py-3">Nombre Completo</th>
                                <th class="py-3">Correo Electrónico</th>
                                <th class="py-3">Rol del Sistema</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No se encontraron registros en la tabla usuarios.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $user): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= htmlspecialchars($user['id']); ?></td>
                                        <td><span class="fw-semibold text-dark"><i class="fas fa-user-circle text-secondary me-2"></i> <?= htmlspecialchars($user['nombre_completo'] ?? 'Sin nombre'); ?></span></td>
                                        <td><span class="text-muted"><?= htmlspecialchars($user['email'] ?? 'Sin correo'); ?></span></td>
                                        <td>
                                            <?php if (strtoupper($user['rol_real'] ?? '') === 'ADMIN'): ?>
                                                <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-shield-alt"></i> ADMIN</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary px-3 py-2"><i class="fas fa-user"></i> <?= htmlspecialchars($user['rol_real'] ?? 'CLIENTE'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>