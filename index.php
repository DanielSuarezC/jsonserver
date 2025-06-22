<?php
require_once 'functions.php';

// Obtener ruta y método
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$resource = $request[0] ?? null;
$id = $request[1] ?? null;

header('Content-Type: application/json');

$data = readDatabase();

if ($resource === 'users') {
    switch ($method) {
        case 'GET':
            if ($id) {
                foreach ($data['users'] as $user) {
                    if ($user['id'] == $id) {
                        echo json_encode($user);
                        exit;
                    }
                }
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            } 
            else {
                echo json_encode($data['users']);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $input['id'] = end($data['users'])['id'] + 1;
            $data['users'][] = $input;
            writeDatabase($data);
            echo json_encode($input);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            foreach ($data['users'] as &$user) {
                if ($user['id'] == $id) {
                    $user = array_merge($user, $input);
                    writeDatabase($data);
                    echo json_encode($user);
                    exit;
                }
            }
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            break;

        case 'DELETE':
            foreach ($data['users'] as $index => $user) {
                if ($user['id'] == $id) {
                    array_splice($data['users'], $index, 1);
                    writeDatabase($data);
                    echo json_encode(['message' => 'User deleted']);
                    exit;
                }
            }
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} 
elseif ($resource === 'usuarios_roles') {
    if ($method === 'GET') {
        $usersWithRoles = [];

        // Crear un mapa de roles para fácil acceso
        $roleMap = [];
        foreach ($data['roles'] as $role) {
            $roleMap[$role['id']] = $role['name'];
        }

        // Agrupar roles por usuario
        $userRolesMap = [];
        foreach ($data['users_roles'] as $ur) {
            $userRolesMap[$ur['user_id']][] = $roleMap[$ur['role_id']] ?? 'UNKNOWN_ROLE';
        }

        // Armar estructura combinada
        foreach ($data['users'] as $user) {
            $userId = $user['id'];
            $userWithRoles = $user;
            $userWithRoles['roles'] = $userRolesMap[$userId] ?? [];
            $usersWithRoles[] = $userWithRoles;
        }

        echo json_encode($usersWithRoles);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}

else {
    http_response_code(404);
    echo json_encode(['error' => 'Resource not found']);
}
