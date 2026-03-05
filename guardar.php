<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

function sanitizar($dato) {
    return htmlspecialchars(trim($dato ?? ''), ENT_QUOTES, 'UTF-8');
}

// Obtener credenciales de variables de entorno (Render)
$db_host = getenv('DB_HOST') ?: 'dpg-d6jmcirh46gs73bgpphg-a.oregon-postgres.render.com';
$db_port = getenv('DB_PORT') ?: '5432';
$db_name = getenv('DB_NAME') ?: 'encuestaaceptacion';
$db_user = getenv('DB_USER') ?: 'encuestaaceptacion_user';
$db_password = getenv('DB_PASSWORD'); // ← Esta VIENE de Render

if (!$db_password) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'Configuración de base de datos incompleta']);
    exit;
}

$datos = [
    'voto_principal' => sanitizar($_POST['voto_principal'] ?? ''),
    'nombre_proyecto' => sanitizar($_POST['nombre_proyecto'] ?? ''),
    'edad' => filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT),
    'estado' => sanitizar($_POST['estado'] ?? ''),
    'ciudad' => sanitizar($_POST['ciudad'] ?? ''),
    'nivel_necesidad' => filter_input(INPUT_POST, 'nivel_necesidad', FILTER_VALIDATE_INT),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Desconocida'
    // ... agrega más campos según necesites
];

try {
    // Conexión con SSL
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;sslmode=require";
    
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $sql = "INSERT INTO respuestas_encuesta (voto_principal, nombre_proyecto, edad, estado, ciudad, nivel_necesidad, ip_address, respondido_en) 
            VALUES (:voto_principal, :nombre_proyecto, :edad, :estado, :ciudad, :nivel_necesidad, :ip_address, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($datos);
    
    echo json_encode(['success' => true, 'mensaje' => 'Respuesta guardada correctamente']);
    
} catch (PDOException $e) {
    error_log("Error DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>
