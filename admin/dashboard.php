<?php
session_start();
// Verificar que sea administrador
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/conexion.php';

// Obtener conteos para las tarjetas del dashboard
try {
    // Total de categorías
    $stmt_cat = $conexion->query("SELECT COUNT(*) FROM categorias");
    $total_categorias = $stmt_cat->fetchColumn();

    // Total de productos
    $stmt_prod = $conexion->query("SELECT COUNT(*) FROM productos");
    $total_productos = $stmt_prod->fetchColumn();

    // Total de usuarios
    $stmt_user = $conexion->query("SELECT COUNT(*) FROM usuarios");
    $total_usuarios = $stmt_user->fetchColumn();

} catch (PDOException $e) {
    $total_categorias = 0;
    $total_productos = 0;
    $total_usuarios = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Barra de Navegación del Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fas fa-cogs"></i> Admin Panel</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['user_nombre']); ?> (ADMIN)</span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>
    </nav>

    <!-- Menú secundario -->
    <div class="bg-white border-bottom shadow-sm mb-4">
        <div class="container py-2">
            <a href="dashboard.php" class="btn btn-primary btn-sm me-2"><i class="fas fa-home"></i> Dashboard</a>
            <a href="categorias.php" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-tags"></i> Gestionar Categorías</a>
            <a href="productos.php" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-box"></i> Gestionar Productos</a>
            <a href="usuarios.php" class="btn btn-outline-info btn-sm me-2"><i class="fas fa-users"></i> Gestionar Usuarios</a>
            <a href="../index.php" class="btn btn-outline-dark btn-sm"><i class="fas fa-globe"></i> Ver Sitio Web</a>
        </div>
    </div>

    <!-- Contenido Principal del Dashboard -->
    <div class="container mb-5">
        <div class="mb-4">
            <h1 class="fw-bold text-secondary">Bienvenida, <?= htmlspecialchars($_SESSION['user_nombre']); ?></h1>
            <p class="text-muted">Panel de control general del sistema de comercio electrónico.</p>
        </div>

        <div class="row g-4">
            <!-- Tarjeta Categorías -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary fw-bold"><i class="fas fa-tags"></i> Categorías</h5>
                        <h2 class="display-6 fw-bold my-3"><?= $total_categorias; ?></h2>
                        <p class="text-muted small mb-3">Registradas en la base</p>
                        <a href="categorias.php" class="btn btn-primary btn-sm fw-bold">Administrar &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Productos -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 border-start border-success border-4">
                    <div class="card-body">
                        <h5 class="card-title text-success fw-bold"><i class="fas fa-box"></i> Productos</h5>
                        <h2 class="display-6 fw-bold my-3"><?= $total_productos; ?></h2>
                        <p class="text-muted small mb-3">Disponibles en el catálogo</p>
                        <a href="productos.php" class="btn btn-success btn-sm fw-bold">Administrar &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Usuarios (Interactiva) -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 border-start border-info border-4">
                    <div class="card-body">
                        <h5 class="card-title text-info fw-bold"><i class="fas fa-users"></i> Usuarios</h5>
                        <h2 class="display-6 fw-bold my-3"><?= $total_usuarios; ?></h2>
                        <p class="text-muted small mb-3">Registrados en el sistema</p>
                        <a href="usuarios.php" class="btn btn-info btn-sm text-white fw-bold">Administrar &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2026 MiTienda. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>