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
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// ============================================
// PARTE 2: OBTENER DATOS
// ============================================
header('Content-Type: application/json');

try {
    // Obtener todas las respuestas
    $sql = "SELECT 
        id, 
        respondido_en, 
        voto_principal, 
        nombre_proyecto, 
        estado, 
        nivel_necesidad
    FROM respuestas_encuesta 
    ORDER BY respondido_en DESC";
    
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll();
    
    // Obtener estadísticas
    $stats_sql = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN voto_principal = 'Sí, totalmente de acuerdo' THEN 1 END) as si_total,
        COUNT(CASE WHEN voto_principal = 'No, en desacuerdo' THEN 1 END) as no_total
    FROM respuestas_encuesta";
    
    $stats_stmt = $pdo->query($stats_sql);
    $estadisticas = $stats_stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'resultados' => $resultados,
        'estadisticas' => $estadisticas
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ]);
}
?>
