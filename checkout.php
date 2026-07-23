<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir tu conexión PDO o MySQLi según uses en el proyecto (aquí mantenemos tu conexión)
$host = 'localhost';
$usuario = 'root';
$password = '';
$base_datos = 'ecommerce_db';

$conexion = new mysqli($host, $usuario, $password, $base_datos);
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 1. VALIDACIÓN ESTRICTA DE SESIÓN: Si no hay usuario logueado, lo mandamos al login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['user_id'] ?? $_SESSION['usuario_id']; 

// Consultar los datos exactos del usuario autenticado en la base de datos
$sql_user = "SELECT nombres, apellidos, email FROM usuarios WHERE id = $usuario_id";
$resultado_user = $conexion->query($sql_user);

$nombre_usuario_db = "";
$email_usuario_db = "";

if ($resultado_user && $resultado_user->num_rows > 0) {
    $fila_user = $resultado_user->fetch_assoc();
    $nombre_usuario_db = trim($fila_user['nombres'] . ' ' . $fila_user['apellidos']);
    $email_usuario_db  = $fila_user['email']; 
} else {
    // Si la sesión tiene un ID que ya no existe en la base de datos, destruimos sesión y al login
    session_destroy();
    header("Location: login.php");
    exit();
}

// Obtener el total enviado desde el carrito
$monto_total = $_POST['total_general'] ?? $_SESSION['total_carrito'] ?? 0.00;

if (isset($_POST['total_general'])) {
    $_SESSION['monto_total_pago'] = $_POST['total_general'];
}
$monto_total = $_SESSION['monto_total_pago'] ?? $monto_total;

// 2. Procesar cuando el usuario hace clic en completar pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_compra'])) {

    $nombre_cliente = $nombre_usuario_db;
    $correo_cliente = $email_usuario_db; 
    $metodo_pago    = $conexion->real_escape_string($_POST['metodo_pago']);
    
    $codigo_pedido = 'PED-' . date('Ymd') . '-' . rand(1000, 9999);
    $estado_pedido = 'PENDIENTE';

    // Insertar pedido en la base de datos vinculado al usuario logueado
    $sql_pedido = "INSERT INTO pedidos (usuario_id, codigo_pedido, fecha_pedido, monto_total, metodo_pago, estado_pedido) 
                   VALUES ($usuario_id, '$codigo_pedido', NOW(), $monto_total, '$metodo_pago', '$estado_pedido')";
                   
    if ($conexion->query($sql_pedido) === TRUE) {
        $id_pedido = $conexion->insert_id;
    } else {
        die("Error al registrar el pedido: " . $conexion->error);
    }

    // 3. Generar PDF de la factura con FPDF
    require(__DIR__ . '/fpdf/fpdf.php');
    
    $pdf = new FPDF();
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->Cell(0, 10, 'MiTienda E-Commerce', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(108, 117, 125);
    $pdf->Cell(0, 5, 'Comprobante de Venta Electronica', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->Cell(100, 6, 'Informacion del Cliente:', 0, 0);
    $pdf->Cell(90, 6, 'Detalles del Pedido:', 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 5, 'Nombre: ' . utf8_decode($nombre_cliente), 0, 0);
    $pdf->Cell(90, 5, 'No. Pedido: ' . $codigo_pedido, 0, 1);
    
    $pdf->Cell(100, 5, 'Correo: ' . $correo_cliente, 0, 0);
    $pdf->Cell(90, 5, 'Fecha: ' . date('Y-m-d H:i:s'), 0, 1);

    $pdf->Cell(100, 5, '', 0, 0);
    $pdf->Cell(90, 5, 'Metodo de Pago: ' . $metodo_pago, 0, 1);
    
    $pdf->Ln(10);

    $pdf->SetFillColor(16, 185, 129);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    
    $pdf->Cell(95, 8, '  Producto', 1, 0, 'L', true);
    $pdf->Cell(30, 8, 'Precio', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Cant.', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Subtotal', 1, 1, 'C', true);

    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetFont('Arial', '', 10);
    
    if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $subtotal_item = $item['precio'] * $item['cantidad'];
            $pdf->Cell(95, 7, '  ' . utf8_decode($item['descripcion']), 1, 0, 'L');
            $pdf->Cell(30, 7, '$' . number_format($item['precio'], 2), 1, 0, 'C');
            $pdf->Cell(25, 7, $item['cantidad'], 1, 0, 'C');
            $pdf->Cell(40, 7, '$' . number_format($subtotal_item, 2), 1, 1, 'C');
        }
    }

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(150, 9, 'TOTAL A PAGAR: ', 1, 0, 'R');
    $pdf->SetTextColor(16, 185, 129);
    $pdf->Cell(40, 9, '$' . number_format($monto_total, 2), 1, 1, 'C');

    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(108, 117, 125);
    $pdf->Cell(0, 5, 'Gracias por su compra en MiTienda.', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Este documento es una representacion digital de su factura.', 0, 1, 'C');

    $carpeta_facturas = __DIR__ . '/facturas/';
    if (!file_exists($carpeta_facturas)) {
        mkdir($carpeta_facturas, 0777, true);
    }

    $pdf_path = $carpeta_facturas . 'factura_' . $codigo_pedido . '.pdf';
    $pdf->Output('F', $pdf_path);

    // 4. Enviar correo usando PHPMailer
    require __DIR__ . '/PHPMailer/src/Exception.php';
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'leidyalmeida9@gmail.com'; 
        $mail->Password   = 'rgmn uwax htnc bzru'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('leidyalmeida9@gmail.com', 'MiTienda E-Commerce');
        $mail->addAddress($correo_cliente, $nombre_cliente); 
        $mail->addAttachment($pdf_path);

        $mail->isHTML(true);
        $mail->Subject = 'Tu factura de compra - Pedido ' . $codigo_pedido;
        $mail->Body    = 'Hola <b>' . $nombre_cliente . '</b>,<br><br>Gracias por tu compra en MiTienda. Adjuntamos la factura correspondiente a tu pedido por un valor total de <b>$' . number_format($monto_total, 2) . '</b>.<br><br>¡Esperamos verte pronto!';

        $mail->send();
        
        unset($_SESSION['carrito']);
        unset($_SESSION['monto_total_pago']);

        header("Location: gracias.php?pedido=" . $id_pedido);
        exit();

    } catch (Exception $e) {
        echo "El mensaje no se pudo enviar. Error de Mailer: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Finalizar Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 40px; }
        .checkout-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="email"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background-color: #e9ecef; }
        .btn-pagar { background: #10B981; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 15px; }
        .btn-pagar:hover { background: #059669; }
        .resumen { background: #f9fafb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h2><i class="fas fa-credit-card"></i> Finalizar Compra</h2>
        
        <div class="resumen">
            <p class="mb-0"><b>Total a Pagar:</b> <span style="color: #10B981; font-size: 20px; font-weight: bold;">$<?php echo number_format($monto_total, 2); ?></span></p>
        </div>

        <form action="checkout.php" method="POST">
            <div class="form-group">
                <label>Comprador (Usuario Autenticado):</label>
                <input type="text" value="<?php echo htmlspecialchars($nombre_usuario_db); ?>" disabled>
            </div>

            <div class="form-group">
                <label>La factura PDF se enviará al correo:</label>
                <input type="email" value="<?php echo htmlspecialchars($email_usuario_db); ?>" disabled>
                <small class="text-muted text-success"><i class="fas fa-check-circle"></i> Se detectó tu sesión activa correctamente.</small>
            </div>

            <div class="form-group mt-3">
                <label for="metodo_pago">Método de Pago:</label>
                <select id="metodo_pago" name="metodo_pago" required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta de Credito">Tarjeta de Crédito</option>
                    <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                </select>
            </div>

            <button type="submit" name="confirmar_compra" class="btn-pagar"><i class="fas fa-paper-plane"></i> Confirmar Pedido y Enviar Factura</button>
        </form>
    </div>
</body>
</html>