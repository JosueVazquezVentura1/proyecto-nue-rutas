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

// ←←← REEMPLAZA ESTO CON LA CONTRASEÑA REAL DE RENDER ←←←
$db_password = 'PEGAR_AQUI_LA_CONTRASEÑA_DE_RENDER';

$datos = [
    'plataforma_respuesta' => sanitizar($_POST['plataforma_respuesta'] ?? ''),
    'edad' => filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT),
    'sexo' => sanitizar($_POST['sexo'] ?? ''),
    'ocupacion' => sanitizar($_POST['ocupacion'] ?? ''),
    'nivel_estudios' => sanitizar($_POST['nivel_estudios'] ?? ''),
    'pais' => sanitizar($_POST['pais'] ?? 'México'),
    'estado' => sanitizar($_POST['estado'] ?? ''),
    'ciudad' => sanitizar($_POST['ciudad'] ?? ''),
    'voto_principal' => sanitizar($_POST['voto_principal'] ?? ''),
    'nombre_proyecto' => sanitizar($_POST['nombre_proyecto'] ?? ''),
    'tipo_sistema' => sanitizar($_POST['tipo_sistema'] ?? ''),
    'tipo_proyecto' => sanitizar($_POST['tipo_proyecto'] ?? ''),
    'nivel_necesidad' => filter_input(INPUT_POST, 'nivel_necesidad', FILTER_VALIDATE_INT),
    'te_beneficia' => sanitizar($_POST['te_beneficia'] ?? ''),
    'impacto_movilidad' => filter_input(INPUT_POST, 'impacto_movilidad', FILTER_VALIDATE_INT),
    'impacto_ambiental' => filter_input(INPUT_POST, 'impacto_ambiental', FILTER_VALIDATE_INT),
    'impacto_seguridad' => filter_input(INPUT_POST, 'impacto_seguridad', FILTER_VALIDATE_INT),
    'relacion_costo_beneficio' => filter_input(INPUT_POST, 'relacion_costo_beneficio', FILTER_VALIDATE_INT),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Desconocida',
];

try {
    $dsn = "pgsql:host=dpg-d6jmcirh46gs73bgpphg-a.oregon-postgres.render.com;port=5432;dbname=encuestaaceptacion;sslmode=require";
    
    $pdo = new PDO($dsn, 'encuestaaceptacion_user', $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    $sql = "INSERT INTO respuestas_encuesta (plataforma_respuesta, edad, sexo, ocupacion, nivel_estudios, pais, estado, ciudad, voto_principal, nombre_proyecto, tipo_sistema, tipo_proyecto, nivel_necesidad, te_beneficia, impacto_movilidad, impacto_ambiental, impacto_seguridad, relacion_costo_beneficio, ip_address, respondido_en) 
            VALUES (:plataforma_respuesta, :edad, :sexo, :ocupacion, :nivel_estudios, :pais, :estado, :ciudad, :voto_principal, :nombre_proyecto, :tipo_sistema, :tipo_proyecto, :nivel_necesidad, :te_beneficia, :impacto_movilidad, :impacto_ambiental, :impacto_seguridad, :relacion_costo_beneficio, :ip_address, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($datos);
    
    echo json_encode(['success' => true, 'mensaje' => 'Respuesta guardada correctamente']);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>
