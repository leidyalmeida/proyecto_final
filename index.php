<?php
session_start();
require_once 'config/conexion.php';

// Obtener los productos junto con el nombre de su categoría desde la base de datos
try {
    $stmt = $conexion->query("SELECT p.*, c.nombre AS categoria_nombre FROM productos p INNER JOIN categorias c ON p.categoria_id = c.id");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiTienda - Catálogo Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-store"></i> MiTienda</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="text-white text-decoration-none me-3">Inicio</a>
                <a href="#productos" class="text-white text-decoration-none me-3">Productos</a>
                <a href="carrito.php" class="text-white text-decoration-none me-3"><i class="fas fa-shopping-cart"></i> Carrito</a>
                <?php if (isset($_SESSION['user_nombre'])): ?>
                    <span class="text-white me-3"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_nombre']); ?></span>
                    <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'ADMIN'): ?>
                        <a href="admin/dashboard.php" class="btn btn-warning btn-sm me-2"><i class="fas fa-cogs"></i> Panel Admin</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm me-2"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Banner Principal -->
    <div class="bg-primary text-white text-center py-5 mb-5 shadow-sm">
        <div class="container py-4">
            <h1 class="display-4 fw-bold">Bienvenido a Nuestra Tienda Online</h1>
            <p class="lead">Encuentra los mejores productos al mejor precio.</p>
            <a href="#productos" class="btn btn-light btn-lg mt-3 fw-bold"><i class="fas fa-box-open"></i> Ver Productos</a>
        </div>
    </div>

    <!-- Sección de Productos -->
    <div class="container mb-5" id="productos">
        <h2 class="mb-4 text-center fw-bold text-secondary">Catálogo de Productos</h2>
        
        <?php if (empty($productos)): ?>
            <div class="alert alert-info text-center" role="alert">
                <i class="fas fa-info-circle"></i> No hay productos disponibles en este momento. ¡Pronto agregaremos más stock!
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($productos as $prod): ?>
                    <div class="col-md-4">
                        <!-- Tarjeta interactiva con atributos data-* para el modal -->
                        <div class="card h-100 shadow-sm border-0 product-card" 
                           style="cursor: pointer;" 
                           data-bs-toggle="modal" 
                           data-bs-target="#productoModal" 
                           data-id="<?= $prod['id']; ?>"
                           data-imagen="assets/img/<?= htmlspecialchars($prod['imagen']); ?>" 
                           data-codigo="<?= htmlspecialchars($prod['codigo']); ?>" 
                           data-categoria="<?= htmlspecialchars($prod['categoria_nombre']); ?>" 
                           data-descripcion="<?= htmlspecialchars($prod['descripcion']); ?>" 
                           data-precio="<?= number_format($prod['precio'], 2); ?>" 
                           data-stock="<?= htmlspecialchars($prod['stock']); ?>">
                           
                            <img src="assets/img/<?= htmlspecialchars($prod['imagen']); ?>" class="card-img-top" alt="<?= htmlspecialchars($prod['descripcion']); ?>" style="height: 200px; object-fit: cover;" onerror="this.src='assets/img/default.png'">
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-secondary align-self-start mb-2"><?= htmlspecialchars($prod['categoria_nombre']); ?></span>
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($prod['descripcion']); ?></h5>
                                <p class="card-text text-muted small mb-2">Código: <?= htmlspecialchars($prod['codigo']); ?></p>
                                <div class="mt-auto">
                                    <span class="text-success fw-bold fs-5 d-block mb-2">$<?= number_format($prod['precio'], 2); ?></span>
                                    <!-- event.stopPropagation() evita que se abra el modal al dar clic directamente al botón -->
                                    <button type="button" class="btn btn-outline-primary w-100 btn-add-cart" data-id="<?= $prod['id']; ?>" onclick="event.stopPropagation();">
                                        <i class="fas fa-cart-plus"></i> Añadir al Carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Detalle de Producto -->
    <div class="modal fade" id="productoModal" tabindex="-1" aria-labelledby="productoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="productoModalLabel"><i class="fas fa-info-circle"></i> Detalle del Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row align-items-center">
                        <!-- Imagen Grande del Producto -->
                        <div class="col-md-6 text-center mb-3 mb-md-0">
                            <img id="modal-img" src="" alt="Imagen ampliada" class="img-fluid rounded-3 shadow-sm border" style="max-height: 320px; object-fit: cover; width: 100%;">
                        </div>
                        <!-- Información detallada -->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span id="modal-categoria" class="badge bg-secondary"></span>
                                <span id="modal-codigo" class="badge bg-light text-dark border ms-1"></span>
                            </div>
                            <h3 id="modal-descripcion" class="fw-bold text-dark mb-3"></h3>
                            <h4 id="modal-precio" class="text-success fw-bold mb-3"></h4>
                            <p class="text-muted mb-4"><i class="fas fa-boxes"></i> Stock disponible: <span id="modal-stock" class="fw-semibold text-dark"></span> unidades</p>
                            
                            <!-- Botón de añadir al carrito dentro del modal -->
                            <button type="button" class="btn btn-primary w-100 btn-add-cart py-2" id="modal-btn-add" data-id="">
                                <i class="fas fa-cart-plus me-2"></i> Añadir al Carrito
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
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

    <!-- Scripts de Bootstrap, jQuery y SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Lógica para rellenar el modal de vista detallada al hacer clic en la tarjeta
        const productoModal = document.getElementById('productoModal');
        if (productoModal) {
            productoModal.addEventListener('show.bs.modal', function (event) {
                const card = event.relatedTarget;
                if (!card) return;
                
                const id = card.getAttribute('data-id');
                const imagen = card.getAttribute('data-imagen');
                const codigo = card.getAttribute('data-codigo');
                const categoria = card.getAttribute('data-categoria');
                const descripcion = card.getAttribute('data-descripcion');
                const precio = card.getAttribute('data-precio');
                const stock = card.getAttribute('data-stock');
                
                productoModal.querySelector('#modal-img').src = imagen;
                productoModal.querySelector('#modal-codigo').textContent = 'Código: ' + codigo;
                productoModal.querySelector('#modal-categoria').textContent = categoria;
                productoModal.querySelector('#modal-descripcion').textContent = descripcion;
                productoModal.querySelector('#modal-precio').textContent = '$' + precio;
                productoModal.querySelector('#modal-stock').textContent = stock;
                
                // Asignar el ID al botón de compra dentro del modal
                productoModal.querySelector('#modal-btn-add').setAttribute('data-id', id);
            });
        }

        // Lógica AJAX unificada para agregar al carrito (funciona en tarjeta y modal)
        $(document).on('click', '.btn-add-cart', function(e) {
            e.preventDefault();
            let producto_id = $(this).data('id');

            if (!producto_id) {
                console.error("ID de producto no encontrado.");
                return;
            }

            $.ajax({
                url: 'ajax/agregar_carrito.php',
                type: 'POST',
                data: { id: producto_id },
                success: function(response) {
                    try {
                        let res = typeof response === 'object' ? response : JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Agregado!',
                                text: res.mensaje,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: res.mensaje
                            });
                        }
                    } catch (err) {
                        console.error("Error al procesar JSON:", err, response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Respuesta inesperada del servidor.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error AJAX:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo comunicar con el servidor.'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>