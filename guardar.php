<?php
/**
 * Backend para guardar respuestas de encuesta
 * Sistema Nacional de Transporte Público
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Función para sanitizar entradas
function sanitizar($dato) {
    if (is_array($dato)) {
        return array_map('sanitizar', $dato);
    }
    return htmlspecialchars(trim($dato ?? ''), ENT_QUOTES, 'UTF-8');
}

// Obtener IP del usuario
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';

// Recibir y sanitizar datos
$datos = [
    'plataforma_respuesta' => sanitizar($_POST['plataforma_respuesta'] ?? ''),
    'edad' => filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT),
    'sexo' => sanitizar($_POST['sexo'] ?? ''),
    'ocupacion' => sanitizar($_POST['ocupacion'] ?? ''),
    'nivel_estudios' => sanitizar($_POST['nivel_estudios'] ?? ''),
    'pais' => sanitizar($_POST['pais'] ?? 'México'),
    'estado' => sanitizar($_POST['estado'] ?? ''),
    'ciudad' => sanitizar($_POST['ciudad'] ?? ''),
    'alcaldia_municipio' => sanitizar($_POST['alcaldia_municipio'] ?? ''),
    'colonia' => sanitizar($_POST['colonia'] ?? ''),
    'codigo_postal' => sanitizar($_POST['codigo_postal'] ?? ''),
    'medio_transporte_principal' => sanitizar($_POST['medio_transporte_principal'] ?? ''),
    'frecuencia_transporte_publico' => sanitizar($_POST['frecuencia_transporte_publico'] ?? ''),
    'satisfaccion_transporte_actual' => filter_input(INPUT_POST, 'satisfaccion_transporte_actual', FILTER_VALIDATE_INT),
    'nombre_proyecto' => sanitizar($_POST['nombre_proyecto'] ?? ''),
    'tipo_sistema' => sanitizar($_POST['tipo_sistema'] ?? ''),
    'tipo_proyecto' => sanitizar($_POST['tipo_proyecto'] ?? ''),
    'trayecto_propuesto' => sanitizar($_POST['trayecto_propuesto'] ?? ''),
    'voto_principal' => sanitizar($_POST['voto_principal'] ?? ''),
    'nivel_necesidad' => filter_input(INPUT_POST, 'nivel_necesidad', FILTER_VALIDATE_INT),
    'te_beneficia' => sanitizar($_POST['te_beneficia'] ?? ''),
    'impacto_movilidad' => filter_input(INPUT_POST, 'impacto_movilidad', FILTER_VALIDATE_INT),
    'impacto_ambiental' => filter_input(INPUT_POST, 'impacto_ambiental', FILTER_VALIDATE_INT),
    'impacto_seguridad' => filter_input(INPUT_POST, 'impacto_seguridad', FILTER_VALIDATE_INT),
    'relacion_costo_beneficio' => filter_input(INPUT_POST, 'relacion_costo_beneficio', FILTER_VALIDATE_INT),
    'recomendaciones_si' => sanitizar($_POST['recomendaciones_si'] ?? ''),
    'consideraciones_parcial' => sanitizar($_POST['consideraciones_parcial'] ?? ''),
    'argumentos_no' => sanitizar($_POST['argumentos_no'] ?? ''),
    'razon_principal_voto' => sanitizar($_POST['razon_principal_voto'] ?? ''),
    'consentimiento_datos' => isset($_POST['consentimiento_datos']) ? true : false,
    'consentimiento_contacto' => isset($_POST['consentimiento_contacto']) ? true : false,
    'email_opcional' => sanitizar($_POST['email_opcional'] ?? ''),
    'ip_address' => $ip_address
];

// Validaciones críticas
$errores = [];
if (empty($datos['voto_principal'])) $errores[] = 'El voto principal es requerido';
if (empty($datos['nombre_proyecto'])) $errores[] = 'El nombre del proyecto es requerido';
if (!$datos['edad'] || $datos['edad'] < 0 || $datos['edad'] > 120) $errores[] = 'Edad inválida';

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errores' => $errores]);
    exit;
}

try {
    // Configuración de conexión CON SSL
    $host = 'dpg-d6jmcirh46gs73bgpphg-a.oregon-postgres.render.com';
    $port = '5432';
    $dbname = 'encuestaaceptacion';
    $user = 'encuestaaceptacion_user';
    $password = 'FCgjnHaRbMuv1FyKwNKrSoK4anHJ4l70';
    
    // DSN con SSL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    // Conexión a PostgreSQL con SSL
    $pdo = new PDO(
        $dsn,
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Consulta SQL
    $sql = "INSERT INTO respuestas_encuesta (
        plataforma_respuesta, edad, sexo, ocupacion, nivel_estudios,
        pais, estado, ciudad, alcaldia_municipio, colonia, codigo_postal,
        medio_transporte_principal, frecuencia_transporte_publico, satisfaccion_transporte_actual,
        nombre_proyecto, tipo_sistema, tipo_proyecto, trayecto_propuesto,
        voto_principal, nivel_necesidad, te_beneficia,
        impacto_movilidad, impacto_ambiental, impacto_seguridad, relacion_costo_beneficio,
        recomendaciones_si, consideraciones_parcial, argumentos_no, razon_principal_voto,
        consentimiento_datos, consentimiento_contacto, email_opcional, ip_address,
        respondido_en
    ) VALUES (
        :plataforma_respuesta, :edad, :sexo, :ocupacion, :nivel_estudios,
        :pais, :estado, :ciudad, :alcaldia_municipio, :colonia, :codigo_postal,
        :medio_transporte_principal, :frecuencia_transporte_publico, :satisfaccion_transporte_actual,
        :nombre_proyecto, :tipo_sistema, :tipo_proyecto, :trayecto_propuesto,
        :voto_principal, :nivel_necesidad, :te_beneficia,
        :impacto_movilidad, :impacto_ambiental, :impacto_seguridad, :relacion_costo_beneficio,
        :recomendaciones_si, :consideraciones_parcial, :argumentos_no, :razon_principal_voto,
        :consentimiento_datos, :consentimiento_contacto, :email_opcional, :ip_address,
        NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($datos);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Respuesta guardada correctamente',
        'id' => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'mensaje' => 'Error al guardar: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'mensaje' => 'Error interno del servidor'
    ]);
}
?>
