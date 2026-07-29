<?php
session_start();
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit;
}
require_once '../config/conexion.php';

$mensaje = "";
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: productos.php");
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_producto'])) {
    $categoria_id = $_POST['categoria_id'];
    $codigo = trim($_POST['codigo']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    
    // Mantener la imagen actual por defecto
    $imagen = $_POST['imagen_actual'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = time() . '_' . $_FILES['imagen']['name'];
        $ruta_destino = '../assets/img/' . $nombre_archivo;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen = $nombre_archivo;
        }
    }

    if (!empty($codigo) && !empty($descripcion) && !empty($precio)) {
        $stmt = $conexion->prepare("UPDATE productos SET categoria_id = :categoria_id, codigo = :codigo, descripcion = :descripcion, precio = :precio, stock = :stock, imagen = :imagen WHERE id = :id");
        $stmt->execute([
            ':categoria_id' => $categoria_id,
            ':codigo' => $codigo,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
            ':imagen' => $imagen,
            ':id' => $id
        ]);
        
        // CORRECCIÓN AQUÍ: Redirigimos enviando el parámetro para activar la alerta flotante
        header("Location: productos.php?mensaje=editado");
        exit;
    }
}

// Obtener datos del producto
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header("Location: productos.php");
    exit;
}

// Obtener categorías
$stmt_cat = $conexion->query("SELECT * FROM categorias WHERE estado = 1");
$categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <h2><i class="fas fa-edit"></i> Editar Producto</h2>
        <hr>
        <div class="card shadow-sm p-4">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="imagen_actual" value="<?= $producto['imagen'] ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" class="form-select" required>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $producto['categoria_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" name="codigo" value="<?= htmlspecialchars($producto['codigo']) ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" step="0.01" class="form-control" name="precio" value="<?= $producto['precio'] ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" class="form-control" name="descripcion" value="<?= htmlspecialchars($producto['descripcion']) ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-control" name="stock" value="<?= $producto['stock'] ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nueva Imagen (Opcional)</label>
                        <input type="file" class="form-control" name="imagen" accept="image/*">
                    </div>
                </div>
                <button type="submit" name="actualizar_producto" class="btn btn-success"><i class="fas fa-save"></i> Guardar Cambios</button>
                <a href="productos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</body>
</html>