<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


// Conectar a SQLite
$db = new PDO("sqlite:dinoWorld.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Identificar el método de solicitud (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Obtener un dinosaurio por ID
            $stmt = $db->prepare("SELECT * FROM dinosaurios WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $dino = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($dino) {
                echo json_encode($dino);
            } else {
                echo json_encode(["message" => "Dinosaurio no encontrado"]);
            }
        } else {
            // Obtener todos los dinosaurios
            $stmt = $db->query("SELECT * FROM dinosaurios");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
         $data = json_decode(file_get_contents("php://input"), true);
         var_dump($data);  // Verifica los datos recibidos
         if (isset($data['nombre'], $data['especie'], $data['periodo'])) {
            $stmt = $db->prepare("INSERT INTO dinosaurios (nombre, especie, periodo, descripcion, imagen) VALUES (?, ?, ?, ?, ?)");
             $stmt->execute([$data['nombre'], $data['especie'], $data['periodo'], $data['descripcion'], $data['imagen']]);
              echo json_encode(["id" => $db->lastInsertId()]);
            } else {
                echo json_encode(["message" => "Datos incompletos para insertar"]);
            }
            break;
        

    case 'PUT':
        // Actualizar un dinosaurio existente
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['nombre'], $data['especie'], $data['periodo'])) {
                $stmt = $db->prepare("UPDATE dinosaurios SET nombre = ?, especie = ?, periodo = ?, descripcion = ?, imagen = ? WHERE id = ?");
                $stmt->execute([$data['nombre'], $data['especie'], $data['periodo'], $data['descripcion'], $data['imagen'], $id]);
                echo json_encode(["message" => "Registro actualizado"]);
            } else {
                echo json_encode(["message" => "Datos incompletos para actualizar"]);
            }
        } else {
            echo json_encode(["message" => "ID no proporcionado"]);
        }
        break;

    case 'DELETE':
        // Eliminar un dinosaurio por ID
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $db->prepare("DELETE FROM dinosaurios WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["message" => "Registro eliminado"]);
        } else {
            echo json_encode(["message" => "ID no proporcionado"]);
        }
        break;

    default:
        // Si el método no es uno de los soportados
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>
