<?php
// ============================================
// PARTE 1: CONEXIÓN A BASE DE DATOS (VA AQUÍ)
// ============================================
$host = 'dpg-d6jmcirh46gs73bgpphg-a.oregon-postgres.render.com';
$port = '5432';
$dbname = 'encuestaaceptacion';
$user = 'encuestaaceptacion_user';
$password = 'FCgjnHaRbMuv1FyKwNKrSoK4anHJ4l70';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// ============================================
// PARTE 2: RECIBIR DATOS DEL FORMULARIO
// ============================================
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Función para sanitizar
function sanitizar($dato) {
    return htmlspecialchars(trim($dato ?? ''), ENT_QUOTES, 'UTF-8');
}

// Recibir datos
$datos = [
    'voto_principal' => sanitizar($_POST['voto_principal'] ?? ''),
    'nombre_proyecto' => sanitizar($_POST['nombre_proyecto'] ?? ''),
    'edad' => filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT),
    'estado' => sanitizar($_POST['estado'] ?? ''),
    'ciudad' => sanitizar($_POST['ciudad'] ?? ''),
    'nivel_necesidad' => filter_input(INPUT_POST, 'nivel_necesidad', FILTER_VALIDATE_INT),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Desconocida'
    // ... agrega más campos según tu formulario
];

// ============================================
// PARTE 3: INSERTAR EN BASE DE DATOS
// ============================================
try {
    $sql = "INSERT INTO respuestas_encuesta 
            (voto_principal, nombre_proyecto, edad, estado, ciudad, nivel_necesidad, ip_address, respondido_en) 
            VALUES 
            (:voto_principal, :nombre_proyecto, :edad, :estado, :ciudad, :nivel_necesidad, :ip_address, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($datos);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Respuesta guardada correctamente'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al guardar: ' . $e->getMessage()
    ]);
}
?>
