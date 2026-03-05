<?php
header('Content-Type: application/json');

try {
    $db_host = getenv('DB_HOST') ?: 'dpg-d6jmcirh46gs73bgpphg-a.oregon-postgres.render.com';
    $db_port = getenv('DB_PORT') ?: '5432';
    $db_name = getenv('DB_NAME') ?: 'encuestaaceptacion';
    $db_user = getenv('DB_USER') ?: 'encuestaaceptacion_user';
    $db_password = getenv('DB_PASSWORD');
    
    if (!$db_password) {
        throw new Exception('DB_PASSWORD no configurada');
    }
    
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;sslmode=require";
    
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $resultados = $pdo->query("SELECT id, voto_principal, nombre_proyecto, estado, respondido_en FROM respuestas_encuesta ORDER BY respondido_en DESC LIMIT 50")->fetchAll();
    
    $stats = $pdo->query("SELECT COUNT(*) as total FROM respuestas_encuesta")->fetch();
    
    echo json_encode(['success' => true, 'resultados' => $resultados, 'estadisticas' => $stats]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>
