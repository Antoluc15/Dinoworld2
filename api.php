<?php
// Habilitar errores para depuración (elimina en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de cabeceras para CORS (si es necesario)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Conexión a la base de datos
$host = '127.0.0.1';
$dbname = 'tu_base_de_datos';
$user = 'tu_usuario';
$password = 'tu_contraseña';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar con la base de datos: ' . $e->getMessage()]);
    exit;
}

// Detectar el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Manejo de rutas y lógica de API
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Consultar un dinosaurio por ID
            $stmt = $pdo->prepare("SELECT * FROM dinosaurios WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $dinosaurio = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($dinosaurio);
        } else {
            // Consultar todos los dinosaurios
            $stmt = $pdo->query("SELECT * FROM dinosaurios");
            $dinosaurios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($dinosaurios);
        }
        break;

    case 'POST':
        // Crear un nuevo dinosaurio
        $data = json_decode(file_get_contents('php://input'), true);
        if (!empty($data['nombre']) && !empty($data['especie']) && !empty($data['periodo'])) {
            $stmt = $pdo->prepare("INSERT INTO dinosaurios (nombre, especie, periodo, descripcion, imagen) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['nombre'],
                $data['especie'],
                $data['periodo'],
                $data['descripcion'] ?? '',
                $data['imagen'] ?? ''
            ]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
        }
        break;

    case 'PUT':
        // Actualizar un dinosaurio existente
        parse_str(file_get_contents('php://input'), $data);
        if (!empty($data['id']) && !empty($data['nombre']) && !empty($data['especie']) && !empty($data['periodo'])) {
            $stmt = $pdo->prepare("UPDATE dinosaurios SET nombre = ?, especie = ?, periodo = ?, descripcion = ?, imagen = ? WHERE id = ?");
            $stmt->execute([
                $data['nombre'],
                $data['especie'],
                $data['periodo'],
                $data['descripcion'] ?? '',
                $data['imagen'] ?? '',
                $data['id']
            ]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
        }
        break;

    case 'DELETE':
        // Eliminar un dinosaurio por ID
        parse_str(file_get_contents('php://input'), $data);
        if (!empty($data['id'])) {
            $stmt = $pdo->prepare("DELETE FROM dinosaurios WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID no proporcionado']);
        }
        break;

    case 'OPTIONS':
        // Manejo de preflight para CORS
        http_response_code(200);
        break;

    default:
        // Método no soportado
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
