<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite solicitudes desde cualquier origen
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$method = $_SERVER['REQUEST_METHOD'];
$dsn = "sqlite:database/dinoworld.db"; // Ruta de la base de datos SQLite

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM dinosaurios WHERE id = :id');
            $stmt->execute(['id' => $_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query('SELECT * FROM dinosaurios');
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO dinosaurios (nombre, especie, periodo, descripcion, imagen) VALUES (:nombre, :especie, :periodo, :descripcion, :imagen)');
        $stmt->execute([
            'nombre' => $data['nombre'],
            'especie' => $data['especie'],
            'periodo' => $data['periodo'],
            'descripcion' => $data['descripcion'],
            'imagen' => $data['imagen']
        ]);
        echo json_encode(['id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare('UPDATE dinosaurios SET nombre = :nombre, especie = :especie, periodo = :periodo, descripcion = :descripcion, imagen = :imagen WHERE id = :id');
            $stmt->execute([
                'id' => $_GET['id'],
                'nombre' => $data['nombre'],
                'especie' => $data['especie'],
                'periodo' => $data['periodo'],
                'descripcion' => $data['descripcion'],
                'imagen' => $data['imagen']
            ]);
            echo json_encode(['success' => true]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('DELETE FROM dinosaurios WHERE id = :id');
            $stmt->execute(['id' => $_GET['id']]);
            echo json_encode(['success' => true]);
        }
        break;

    case 'OPTIONS':
        // Responder para solicitudes preflight
        http_response_code(200);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
