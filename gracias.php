<?php
$id_pedido = $_GET['pedido'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compra Exitosa</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 50px; text-align: center; }
        .success-box { background: white; padding: 40px; border-radius: 8px; display: inline-block; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h1 { color: #10B981; }
        .btn-volver { background: #4F46E5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-block; margin-top: 20px; }
        .btn-volver:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="success-box">
        <h1>¡Compra Realizada con Éxito!</h1>
        <p>Tu pedido ha sido procesado correctamente.</p>
        <?php if (!empty($id_pedido)): ?>
            <p>Número de registro interno: <b>#<?php echo htmlspecialchars($id_pedido); ?></b></p>
        <?php endif; ?>
        <p>Hemos generado tu factura en PDF y te la hemos enviado a tu correo electrónico.</p>
        <a href="index.php" class="btn-volver">Volver a la Tienda</a>
    </div>
</body>
</html>