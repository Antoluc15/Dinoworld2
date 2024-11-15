<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Conectar a SQLite
$db = new PDO("sqlite:dinoWorld.db");

// Identificar el método de solicitud (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("SELECT * FROM dinosaurios WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $db->query("SELECT * FROM dinosaurios");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $db->prepare("INSERT INTO dinosaurios (nombre, especie, periodo, descripcion, imagen) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['nombre'], $data['especie'], $data['periodo'], $data['descripcion'], $data['imagen']]);
        echo json_encode(["id" => $db->lastInsertId()]);
        break;

        
    case 'PUT':
        $id = $_GET['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $db->prepare("UPDATE dinosaurios SET nombre = ?, especie = ?, periodo = ?, descripcion = ?, imagen = ? WHERE id = ?");
        $stmt->execute([$data['nombre'], $data['especie'], $data['periodo'], $data['descripcion'], $data['imagen'], $id]);
        echo json_encode(["message" => "Registro actualizado"]);
        break;
        
    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $db->prepare("DELETE FROM dinosaurios WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Registro eliminado"]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>
