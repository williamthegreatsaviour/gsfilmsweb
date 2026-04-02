<?php
global $db, $userModel;

$method = $_SERVER['REQUEST_METHOD'];
$action = $segments[1] ?? '';

if ($method === 'POST' && $action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        jsonResponse(['error' => 'Email and password required'], 400);
    }
    
    $user = $userModel->verifyPassword($email, $password);
    
    if ($user) {
        $token = $userModel->generateToken($user['id']);
        jsonResponse([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar' => $user['avatar']
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Invalid credentials'], 401);
    }
}

if ($method === 'POST' && $action === 'register') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        jsonResponse(['error' => 'All fields required'], 400);
    }
    
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        jsonResponse(['error' => 'Email already registered'], 400);
    }
    
    $userId = $userModel->create([
        'name' => $name,
        'email' => $email,
        'password' => $password
    ]);
    
    $token = $userModel->generateToken($userId);
    $user = $userModel->findById($userId);
    
    jsonResponse([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ], 201);
}

if ($method === 'GET' && $action === 'me') {
    $user = requireAuth();
    jsonResponse([
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar' => $user['avatar'],
        'created_at' => $user['created_at']
    ]);
}

jsonResponse(['error' => 'Invalid endpoint'], 404);
