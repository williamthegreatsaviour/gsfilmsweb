<?php
require_once dirname(__DIR__) . '/config/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'client'
        ]);
        return $this->db->lastInsertId();
    }
    
    public function findByEmail($email) {
        return $this->db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    }
    
    public function findById($id) {
        return $this->db->fetchOne("SELECT id, name, email, role, avatar, created_at FROM users WHERE id = ?", [$id]);
    }
    
    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->query($sql, $params);
    }
    
    public function getAll($role = null) {
        if ($role) {
            return $this->db->fetchAll("SELECT id, name, email, role, created_at FROM users WHERE role = ? ORDER BY created_at DESC", [$role]);
        }
        return $this->db->fetchAll("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    }
    
    public function isAdmin($user) {
        return in_array($user['role'], ['super_admin', 'moderator']);
    }
    
    public function isSuperAdmin($user) {
        return $user['role'] === 'super_admin';
    }
    
    public function createJWT($user) {
        $payload = [
            'iss' => \Config::getBaseUrl(),
            'aud' => \Config::getBaseUrl(),
            'iat' => time(),
            'exp' => time() + \Config::getJwtExpiry(),
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
        
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = base64_encode(hash_hmac('sha256', "{$header}.{$payloadEncoded}", \Config::getJwtSecret(), true));
        
        return "{$header}.{$payloadEncoded}.{$signature}";
    }
    
    public function generateToken($userId) {
        $user = $this->findById($userId);
        if (!$user) return null;
        return $this->createJWT($user);
    }
    
    public function verifyToken($token) {
        $payload = $this->verifyJWT($token);
        if (!$payload || !isset($payload['user'])) return false;
        return $this->findById($payload['user']['id']);
    }
    
    public function verifyJWT($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        
        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];
        
        $expectedSignature = base64_encode(hash_hmac('sha256', "{$header}.{$payload}", \Config::getJwtSecret(), true));
        if ($signature !== $expectedSignature) return false;
        
        $payloadData = json_decode(base64_decode($payload), true);
        if ($payloadData['exp'] < time()) return false;
        
        return $payloadData;
    }
}
