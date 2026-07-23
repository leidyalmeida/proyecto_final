<?php
session_start();
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit;
}
require_once '../config/conexion.php';

$mensaje = "";

// Procesar el formulario para crear un producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_producto'])) {
    $categoria_id = $_POST['categoria_id'];
    $codigo = trim($_POST['codigo']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    
    // Manejo de la imagen
    $imagen = 'default.png';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = time() . '_' . $_FILES['imagen']['name'];
        $ruta_destino = '../assets/img/' . $nombre_archivo;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen = $nombre_archivo;
        }
    }

    if (!empty($codigo) && !empty($descripcion) && !empty($precio)) {
        $stmt = $conexion->prepare("INSERT INTO productos (categoria_id, codigo, descripcion, precio, stock, imagen) VALUES (:categoria_id, :codigo, :descripcion, :precio, :stock, :imagen)");
        $stmt->execute([
            ':categoria_id' => $categoria_id,
            ':codigo' => $codigo,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
            ':imagen' => $imagen
        ]);
        $mensaje = "¡Producto registrado con éxito!";
    }
}

// Obtener categorías para el select
$stmt_cat = $conexion->query("SELECT * FROM categorias WHERE estado = 1");
$categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos
$stmt_prod = $conexion->query("SELECT p.*, c.nombre AS categoria_nombre FROM productos p INNER JOIN categorias c ON p.categoria_id = c.id");
$productos = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Productos | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-cogs"></i> Admin Panel</a>
            <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <h2><i class="fas fa-box"></i> Gestión de Productos</h2>
        <hr>
        <?php if(!empty($mensaje)): ?>
            <div class="alert alert-success"><?= $mensaje ?></div>
        <?php endif; ?>

        <!-- Formulario de Registro -->
        <div class="card shadow-sm p-4 mb-4">
            <h4 class="mb-3">Agregar Nuevo Producto</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" name="codigo" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" step="0.01" class="form-control" name="precio" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Descripción del Producto</label>
                        <input type="text" class="form-control" name="descripcion" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-control" name="stock" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Imagen</label>
                        <input type="file" class="form-control" name="imagen" accept="image/*">
                    </div>
                </div>
                <button type="submit" name="guardar_producto" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Producto</button>
            </form>
        </div>

        <!-- Listado de Productos -->
        <div class="card shadow-sm p-4">
            <h4 class="mb-3">Productos Registrados</h4>
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $prod): ?>
                    <tr>
                        <td><img src="../assets/img/<?= htmlspecialchars($prod['imagen']) ?>" width="50" height="50" style="object-fit:cover;" onerror="this.src='https://via.placeholder.com/50?text=Error'"></td>
                        <td><?= htmlspecialchars($prod['codigo']) ?></td>
                        <td><?= htmlspecialchars($prod['descripcion']) ?></td>
                        <td><?= htmlspecialchars($prod['categoria_nombre']) ?></td>
                        <td>$<?= number_format($prod['precio'], 2) ?></td>
                        <td><?= $prod['stock'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <a href="dashboard.php" class="btn btn-secondary">&larr; Volver al Dashboard</a>
        </div>
    </div>
</body>
</html>