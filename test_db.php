<?php
// test_db.php - Solo para diagnóstico (ELIMINAR DESPUÉS)

header('Content-Type: text/plain');

$db_host = getenv('DB_HOST') ?: 'dpg-d6jmcirh46gs73bgpphg-a.oregon-postgres.render.com';
$db_port = getenv('DB_PORT') ?: '5432';
$db_name = getenv('DB_NAME') ?: 'encuestaaceptacion';
$db_user = getenv('DB_USER') ?: 'encuestaaceptacion_user';
$db_password = getenv('DB_PASSWORD');

echo "=== Diagnóstico de Conexión ===\n";
echo "Host: $db_host\n";
echo "Port: $db_port\n";
echo "Database: $db_name\n";
echo "User: $db_user\n";
echo "Password set: " . ($db_password ? 'YES (length: '.strlen($db_password).')' : 'NO') . "\n\n";

if (!$db_password) {
    echo "❌ ERROR: DB_PASSWORD no está configurada en Render\n";
    exit;
}

try {
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;sslmode=require";
    echo "Intentando conectar con DSN: $dsn\n\n";
    
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "✅ ¡Conexión exitosa!\n";
    echo "Versión PostgreSQL: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    $count = $pdo->query("SELECT COUNT(*) FROM respuestas_encuesta")->fetchColumn();
    echo "Registros en tabla: $count\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión:\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "Message: " . $e->getMessage() . "\n";
}
?>
