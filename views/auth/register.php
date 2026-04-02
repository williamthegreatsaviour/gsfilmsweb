<?php
// Registro de usuario
session_start();

// Incluir configuración
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

$db = Database::getInstance();
$userModel = new User();

// Variable para usuario actual
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = $userModel->findById($_SESSION['user_id']);
}

// Funciones de utilidad
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['super_admin', 'moderator']);
}

$pageTitle = 'Registro';
$registerError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $registerError = 'Por favor completa todos los campos';
    } elseif ($password !== $confirmPassword) {
        $registerError = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $registerError = 'La contraseña debe tener al menos 6 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = 'El email no es válido';
    } else {
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser) {
            $registerError = 'El email ya está registrado';
        } else {
            $userId = $userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'client'
            ]);
            
            if ($userId) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'client';
                
                header('Location: ?route=home');
                exit;
            } else {
                $registerError = 'Error al crear la cuenta. Intenta de nuevo.';
            }
        }
    }
}

// Incluir header
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="min-h-screen flex items-center justify-center px-4 py-20">
    <div class="hud-container rounded-2xl p-8 w-full max-w-md gradient-border">
        <?php if (!empty($registerError)): ?>
        <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6 text-center">
            <?= htmlspecialchars($registerError) ?>
        </div>
        <?php endif; ?>
        
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-gold to-gold-dark flex items-center justify-center shadow-glow mb-4">
                <i class="fas fa-user-plus text-black text-3xl"></i>
            </div>
            <h2 class="font-orbitron text-3xl font-bold text-gold-gradient">CREAR CUENTA</h2>
            <p class="text-gray-400 font-rajdhani mt-2">Únete a GSFilms</p>
        </div>
        
        <form action="?route=register" method="POST" class="space-y-6">
            <div>
                <label class="block text-gold font-rajdhani mb-2">Nombre</label>
                <input type="text" name="name" required 
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       placeholder="Tu nombre">
            </div>
            
            <div>
                <label class="block text-gold font-rajdhani mb-2">Email</label>
                <input type="email" name="email" required 
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       placeholder="tu@email.com">
            </div>
            
            <div>
                <label class="block text-gold font-rajdhani mb-2">Contraseña</label>
                <input type="password" name="password" required minlength="6"
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       placeholder="Mínimo 6 caracteres">
            </div>
            
            <div>
                <label class="block text-gold font-rajdhani mb-2">Confirmar Contraseña</label>
                <input type="password" name="confirm_password" required
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       placeholder="Repite tu contraseña">
            </div>
            
            <button type="submit" class="hud-button w-full py-3 rounded-lg font-rajdhani font-bold text-lg">
                <i class="fas fa-user-plus mr-2"></i>Registrarse
            </button>
        </form>
        
        <div class="text-center mt-6">
            <p class="text-gray-400 font-rajdhani">
                ¿Ya tienes cuenta? 
                <a href="?route=login" class="text-gold hover:text-gold-light font-semibold">
                    Inicia sesión
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
