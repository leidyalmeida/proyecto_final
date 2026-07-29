<?php
session_start();
require_once 'config/conexion.php';

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar acciones de actualización o eliminación
if (isset($_POST['accion'])) {
    $key = $_POST['key'] ?? '';

    if ($_POST['accion'] === 'actualizar') {
        $nueva_cantidad = intval($_POST['cantidad']);
        $nueva_talla = isset($_POST['talla']) ? trim($_POST['talla']) : '';

        if (isset($_SESSION['carrito'][$key])) {
            if ($nueva_cantidad > 0) {
                // Actualizamos directamente la cantidad y la talla sin alterar la llave existente
                $_SESSION['carrito'][$key]['cantidad'] = $nueva_cantidad;
                if ($nueva_talla !== '') {
                    $_SESSION['carrito'][$key]['talla'] = $nueva_talla;
                }
                echo json_encode(['status' => 'success']);
            } else {
                unset($_SESSION['carrito'][$key]);
                echo json_encode(['status' => 'success']);
            }
        }
        exit();
    }

    if ($_POST['accion'] === 'eliminar') {
        if (isset($_SESSION['carrito'][$key])) {
            unset($_SESSION['carrito'][$key]);
            echo json_encode(['status' => 'success']);
        }
        exit();
    }

    if ($_POST['accion'] === 'vaciar') {
        $_SESSION['carrito'] = [];
        echo json_encode(['status' => 'success']);
        exit();
    }
}

// Calcular subtotales y totales para la vista
$subtotal_general = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiTienda - Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-store"></i> MiTienda</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="text-white text-decoration-none me-3">Inicio</a>
                <a href="carrito.php" class="text-white text-decoration-none me-3"><i class="fas fa-shopping-cart"></i> Carrito</a>
                <?php if (isset($_SESSION['user_nombre'])): ?>
                    <span class="text-white me-3"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_nombre']); ?></span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm me-2"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Contenido del Carrito -->
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-secondary"><i class="fas fa-shopping-cart"></i> Carrito de Compras</h2>
            <a href="index.php" class="btn btn-outline-dark btn-sm fw-bold"><i class="fas fa-arrow-left"></i> Seguir Comprando</a>
        </div>

        <?php if (empty($_SESSION['carrito'])): ?>
            <div class="alert alert-info text-center py-5 shadow-sm rounded-4 bg-white" role="alert">
                <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                <h4 class="fw-bold">Tu carrito está vacío</h4>
                <p class="text-muted">Explora nuestro catálogo y añade productos para empezar.</p>
                <a href="index.php" class="btn btn-primary mt-2 rounded-pill px-4">Ver Productos</a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Tabla de Productos en el Carrito -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="py-3 ps-3">Imagen</th>
                                        <th class="py-3">Producto / Opciones</th>
                                        <th class="py-3 text-center">Precio</th>
                                        <th class="py-3 text-center" style="width: 140px;">Cantidad</th>
                                        <th class="py-3 text-center">Subtotal</th>
                                        <th class="py-3 text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['carrito'] as $key => $item): 
                                        $subtotal = $item['precio'] * $item['cantidad'];
                                        $subtotal_general += $subtotal;
                                    ?>
                                        <tr data-key="<?= htmlspecialchars($key); ?>">
                                            <td class="ps-3 py-3" style="width: 90px;">
                                                <img src="assets/img/<?= htmlspecialchars($item['imagen']); ?>" alt="" class="img-fluid rounded-3 border" style="width: 70px; height: 70px; object-fit: cover;" onerror="this.src='assets/img/default.png'">
                                            </td>
                                            <td>
                                                <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($item['descripcion']); ?></h6>
                                                <small class="text-muted d-block">Código: <?= htmlspecialchars($item['codigo']); ?></small>
                                                
                                                <!-- Selector de tallas: Solo aparece si el producto tiene talla -->
                                                <?php if (!empty($item['talla'])): ?>
                                                    <div class="mt-2 d-flex align-items-center gap-1">
                                                        <span class="small fw-semibold text-secondary">Talla:</span>
                                                        <select class="form-select form-select-sm select-talla py-0 px-2" style="width: 80px; font-size: 12px;">
                                                            <?php foreach (['XS', 'S', 'M', 'L', 'XXL'] as $t): ?>
                                                                <option value="<?= $t; ?>" <?= $item['talla'] === $t ? 'selected' : ''; ?>><?= $t; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-semibold text-success">$<?= number_format($item['precio'], 2); ?></td>
                                            <td class="text-center">
                                                <input type="number" class="form-control text-center input-cantidad fw-bold mx-auto" value="<?= $item['cantidad']; ?>" min="1" style="width: 75px;">
                                            </td>
                                            <td class="text-center fw-bold text-success">$<?= number_format($subtotal, 2); ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle btn-eliminar" title="Eliminar producto">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-light d-flex justify-content-between p-3">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="btn-vaciar"><i class="fas fa-trash"></i> Vaciar Carrito</button>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-plus"></i> Añadir más productos</a>
                        </div>
                    </div>
                </div>

                <!-- Resumen de la Orden -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
                        <h4 class="fw-bold mb-4 text-dark">Resumen de la Orden</h4>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Subtotal:</span>
                            <span class="fw-semibold text-dark">$<?= number_format($subtotal_general, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Envío:</span>
                            <span class="text-success fw-semibold">Gratis</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fs-5 fw-bold text-dark">Total a Pagar:</span>
                            <span class="fs-4 fw-bold text-success">$<?= number_format($subtotal_general, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> Proceder al Pago
                        </a>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        // Cambiar cantidad en tiempo real
        $(document).on('change', '.input-cantidad', function() {
            let row = $(this).closest('tr');
            let key = row.attr('data-key');
            let cantidad = $(this).val();
            
            let selectTalla = row.find('.select-talla');
            let talla = selectTalla.length ? selectTalla.val() : '';

            if (cantidad < 1) {
                $(this).val(1);
                cantidad = 1;
            }

            $.ajax({
                url: 'carrito.php',
                type: 'POST',
                data: { accion: 'actualizar', key: key, cantidad: cantidad, talla: talla },
                success: function() {
                    location.reload();
                }
            });
        });

        // Cambiar talla de ropa en tiempo real
        $(document).on('change', '.select-talla', function() {
            let row = $(this).closest('tr');
            let key = row.attr('data-key');
            let cantidad = row.find('.input-cantidad').val();
            let talla = $(this).val();

            $.ajax({
                url: 'carrito.php',
                type: 'POST',
                data: { accion: 'actualizar', key: key, cantidad: cantidad, talla: talla },
                success: function() {
                    location.reload();
                }
            });
        });

        // Eliminar producto individual
        $(document).on('click', '.btn-eliminar', function() {
            let row = $(this).closest('tr');
            let key = row.attr('data-key');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se eliminará este producto del carrito",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'carrito.php',
                        type: 'POST',
                        data: { accion: 'eliminar', key: key },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        });

        // Vaciar carrito
        $('#btn-vaciar').click(function() {
            Swal.fire({
                title: '¿Vaciar carrito?',
                text: "Se eliminarán todos los productos agregados",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, vaciar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'carrito.php',
                        type: 'POST',
                        data: { accion: 'vaciar' },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>