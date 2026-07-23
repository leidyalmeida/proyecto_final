<?php
session_start();
require_once 'config/conexion.php';

// Manejar acciones del carrito (eliminar producto o vaciar carrito)
if (isset($_GET['accion'])) {
    if ($_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
        $id = $_GET['id'];
        unset($_SESSION['carrito'][$id]);
        header("Location: carrito.php");
        exit;
    }
    if ($_GET['accion'] == 'vaciar') {
        unset($_SESSION['carrito']);
        header("Location: carrito.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiTienda - Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-store"></i> MiTienda</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="text-white text-decoration-none me-3"><i class="fas fa-arrow-left"></i> Seguir Comprando</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4 fw-bold text-secondary"><i class="fas fa-shopping-cart"></i> Carrito de Compras</h2>

        <?php if (empty($_SESSION['carrito'])): ?>
            <div class="alert alert-info text-center py-4" role="alert">
                <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                <h4>Tu carrito está vacío</h4>
                <p class="text-muted">No has agregado ningún producto todavía.</p>
                <a href="index.php" class="btn btn-primary mt-2"><i class="fas fa-box-open"></i> Ver Catálogo</a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Listado de productos en el carrito -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 p-4 mb-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Imagen</th>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_general = 0;
                                    foreach ($_SESSION['carrito'] as $item): 
                                        $subtotal = $item['precio'] * $item['cantidad'];
                                        $total_general += $subtotal;
                                    ?>
                                    <tr>
                                        <td>
                                            <img src="assets/img/<?= htmlspecialchars($item['imagen']); ?>" width="50" height="50" style="object-fit:cover; border-radius: 5px;" onerror="this.src='assets/img/audifonos.jpg'">
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($item['descripcion']); ?></td>
                                        <td>$<?= number_format($item['precio'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-secondary px-3 py-2 fs-6"><?= $item['cantidad']; ?></span>
                                        </td>
                                        <td class="fw-bold text-success">$<?= number_format($subtotal, 2); ?></td>
                                        <td>
                                            <a href="carrito.php?accion=eliminar&id=<?= $item['id']; ?>" class="btn btn-danger btn-sm" title="Eliminar producto">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                            <div>
                                <a href="carrito.php?accion=vaciar" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i> Vaciar Carrito
                                </a>
                                <a href="index.php" class="btn btn-outline-secondary btn-sm ms-1">
                                    <i class="fas fa-plus"></i> Agregar más productos
                                </a>
                            </div>
                            <div>
                                <a href="exportar_productos.php" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> Descargar Inventario en Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de compra -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 p-4">
                        <h4 class="mb-3 fw-bold">Resumen de la Orden</h4>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold">$<?= number_format($total_general, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Envío:</span>
                            <span class="text-success fw-bold">Gratis</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total a Pagar:</span>
                            <span class="fw-bold fs-4 text-success">$<?= number_format($total_general, 2); ?></span>
                        </div>
                        
                        <!-- Formulario para enviar el total real por POST hacia checkout.php -->
                        <form action="checkout.php" method="POST">
                            <input type="hidden" name="total_general" value="<?= $total_general; ?>">
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                                <i class="fas fa-check-circle"></i> Proceder al Pago
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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