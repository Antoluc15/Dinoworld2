<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}

// Encabezado para permitir solicitudes CORS
header('Content-Type: application/json');

// Función para manejar el método POST (agregar dinosaurio)
function addDino($data) {
    global $pdo;

    $sql = "INSERT INTO dinosaurios (nombre, especie, periodo, descripcion, imagen) 
            VALUES (:nombre, :especie, :periodo, :descripcion, :imagen)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nombre', $data['nombre']);
    $stmt->bindParam(':especie', $data['especie']);
    $stmt->bindParam(':periodo', $data['periodo']);
    $stmt->bindParam(':descripcion', $data['descripcion']);
    $stmt->bindParam(':imagen', $data['imagen']);

    if ($stmt->execute()) {
        $data['id'] = $pdo->lastInsertId(); // Obtener el ID del dinosaurio recién insertado
        return $data;
    } else {
        return false;
    }
}

// Función para manejar el método GET (obtener todos los dinosaurios o uno solo)
function getDinos($id = null) {
    global $pdo;

    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM dinosaurios WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->query("SELECT * FROM dinosaurios");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Función para manejar el método PUT (editar dinosaurio)
function editDino($id, $data) {
    global $pdo;

    $sql = "UPDATE dinosaurios SET 
                nombre = :nombre, 
                especie = :especie, 
                periodo = :periodo, 
                descripcion = :descripcion, 
                imagen = :imagen 
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nombre', $data['nombre']);
    $stmt->bindParam(':especie', $data['especie']);
    $stmt->bindParam(':periodo', $data['periodo']);
    $stmt->bindParam(':descripcion', $data['descripcion']);
    $stmt->bindParam(':imagen', $data['imagen']);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Función para manejar el método DELETE (eliminar dinosaurio)
function deleteDino($id) {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM dinosaurios WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Obtener el método HTTP actual
$method = $_SERVER['REQUEST_METHOD'];

// Manejar la solicitud según el método
switch ($method) {
    case 'GET':
        // Si se proporciona un ID, devolver solo un dinosaurio, de lo contrario, devolver todos
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            echo json_encode(getDinos($id));
        } else {
            echo json_encode(getDinos());
        }
        break;

    case 'POST':
        // Obtener los datos JSON enviados
        $data = json_decode(file_get_contents("php://input"), true);

        // Agregar el dinosaurio
        $result = addDino($data);
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'Error al agregar dinosaurio.']);
        }
        break;

    case 'PUT':
        // Obtener el ID y los datos JSON enviados
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $data = json_decode(file_get_contents("php://input"), true);

            // Editar el dinosaurio
            $result = editDino($id, $data);
            if ($result) {
                echo json_encode(['message' => 'Dinosaurio actualizado correctamente.']);
            } else {
                echo json_encode(['error' => 'Error al actualizar dinosaurio.']);
            }
        } else {
            echo json_encode(['error' => 'ID de dinosaurio no especificado.']);
        }
        break;

    case 'DELETE':
        // Obtener el ID
        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            // Eliminar el dinosaurio
            $result = deleteDino($id);
            if ($result) {
                echo json_encode(['message' => 'Dinosaurio eliminado correctamente.']);
            } else {
                echo json_encode(['error' => 'Error al eliminar dinosaurio.']);
            }
        } else {
            echo json_encode(['error' => 'ID de dinosaurio no especificado.']);
        }
        break;

    default:
        echo json_encode(['error' => 'Método HTTP no permitido.']);
        break;
}
?>

