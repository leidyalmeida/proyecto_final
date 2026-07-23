<?php
session_start();
// Verificar que sea administrador
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/conexion.php';

// Inicializar variables por defecto
$total_categorias = 0;
$total_productos = 0;
$total_usuarios = 0;
$ingresos_totales = 0;
$total_pedidos = 0;
$ultimos_pedidos = [];

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

    // Métricas de ventas
    $stmt_ventas = $conexion->query("SELECT SUM(monto_total) AS ingresos_totales, COUNT(*) AS total_pedidos FROM pedidos");
    $res_ventas = $stmt_ventas->fetch(PDO::FETCH_ASSOC);
    $ingresos_totales = $res_ventas['ingresos_totales'] ?? 0;
    $total_pedidos = $res_ventas['total_pedidos'] ?? 0;

    // Listado de pedidos (Cambiado a un SELECT simple de la tabla pedidos para evitar problemas con el INNER/LEFT JOIN de usuarios)
  $stmt_pedidos = $conexion->query("SELECT id, usuario_id, codigo_pedido, fecha_pedido AS fecha, monto_total AS total, estado_pedido AS estado FROM pedidos ORDER BY id DESC");
    $ultimos_pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_bd = $e->getMessage();
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

        <!-- Tarjetas de métricas -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 border-start border-success border-4">
                    <div class="card-body">
                        <h5 class="card-title text-success fw-bold"><i class="fas fa-dollar-sign"></i> Ventas Totales</h5>
                        <h2 class="display-6 fw-bold my-3">$<?= number_format($ingresos_totales, 2); ?></h2>
                        <p class="text-muted small mb-0"><?= $total_pedidos; ?> pedidos en el sistema</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary fw-bold"><i class="fas fa-tags"></i> Categorías</h5>
                        <h2 class="display-6 fw-bold my-3"><?= $total_categorias; ?></h2>
                        <p class="text-muted small mb-3">Registradas en la base</p>
                        <a href="categorias.php" class="btn btn-primary btn-sm fw-bold">Administrar &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 border-start border-success border-4">
                    <div class="card-body">
                        <h5 class="card-title text-success fw-bold"><i class="fas fa-box"></i> Productos</h5>
                        <h2 class="display-6 fw-bold my-3"><?= $total_productos; ?></h2>
                        <p class="text-muted small mb-3">Disponibles en el catálogo</p>
                        <a href="productos.php" class="btn btn-success btn-sm fw-bold">Administrar &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
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

        <!-- Sección: Control de Estados de Pedidos -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary"><i class="fas fa-shopping-bag"></i> Control de Estados de Pedidos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th># ID</th>
                                <th>Cliente ID</th>
                                <th>Código</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimos_pedidos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No hay pedidos registrados todavía.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ultimos_pedidos as $pedido): ?>
                                    <tr>
                                        <td>#<?= $pedido['id']; ?></td>
                                        <td><span class="badge bg-light text-dark">Usuario #<?= $pedido['usuario_id']; ?></span></td>
                                        <td><?= htmlspecialchars($pedido['codigo_pedido'] ?? 'N/A'); ?></td>
                                        <td class="fw-bold text-success">$<?= number_format($pedido['total'], 2); ?></td>
                                        <td><?= $pedido['fecha']; ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($pedido['estado']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <form action="actualizar_estado.php" method="POST" class="d-inline-flex gap-2">
                                                <input type="hidden" name="pedido_id" value="<?= $pedido['id']; ?>">
                                                <select name="nuevo_estado" class="form-select form-select-sm" style="width: 130px;">
                                                    <option value="PENDIENTE" <?= $pedido['estado'] == 'PENDIENTE' ? 'selected' : ''; ?>>PENDIENTE</option>
                                                    <option value="PROCESO" <?= $pedido['estado'] == 'PROCESO' ? 'selected' : ''; ?>>PROCESO</option>
                                                    <option value="COMPLETADO" <?= $pedido['estado'] == 'COMPLETADO' ? 'selected' : ''; ?>>COMPLETADO</option>
                                                    <option value="CANCELADO" <?= $pedido['estado'] == 'CANCELADO' ? 'selected' : ''; ?>>CANCELADO</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Actualizar</button>
                                            </form>
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

    <!-- Pie de página -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2026 MiTienda. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

