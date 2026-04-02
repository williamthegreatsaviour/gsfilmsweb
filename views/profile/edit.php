<?php
$pageTitle = 'Editar Perfil';

if (!isLoggedIn()) {
    header('Location: ?route=login');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($name)) {
        $error = 'El nombre es obligatorio';
    } else {
        $userModel->update($_SESSION['user_id'], ['name' => $name]);
        $_SESSION['user_name'] = $name;
        $success = 'Nombre actualizado correctamente';
    }
    
    if (!empty($currentPassword) && !empty($newPassword)) {
        $user = $userModel->findById($_SESSION['user_id']);
        
        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'La contraseña actual es incorrecta';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Las nuevas contraseñas no coinciden';
        } elseif (strlen($newPassword) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres';
        } else {
            $userModel->update($_SESSION['user_id'], ['password' => $newPassword]);
            $success = $success ? $success . ' y contraseña actualizada' : 'Contraseña actualizada correctamente';
        }
    }
}

$currentUser = $userModel->findById($_SESSION['user_id']);

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="max-w-2xl mx-auto">
        <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
            <i class="fas fa-user-edit mr-3"></i>Editar Perfil
        </h1>
        
        <?php if ($error): ?>
        <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-lg mb-6">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>
        
        <div class="hud-container rounded-xl p-6 mb-6">
            <h2 class="font-orbitron text-xl text-gold mb-4">Información Personal</h2>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Nombre</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($currentUser['name']) ?>" required 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="Tu nombre">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Email</label>
                    <input type="email" value="<?= htmlspecialchars($currentUser['email']) ?>" disabled 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani opacity-50"
                           placeholder="tu@email.com">
                    <p class="text-gray-500 text-sm mt-1">El email no se puede cambiar</p>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="hud-button px-6 py-3 rounded-lg font-rajdhani font-bold">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        
        <div class="hud-container rounded-xl p-6">
            <h2 class="font-orbitron text-xl text-gold mb-4">Cambiar Contraseña</h2>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Contraseña Actual</label>
                    <input type="password" name="current_password" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="••••••••">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Nueva Contraseña</label>
                    <input type="password" name="new_password" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="••••••••">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Confirmar Nueva Contraseña</label>
                    <input type="password" name="confirm_password" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="••••••••">
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="hud-button px-6 py-3 rounded-lg font-rajdhani font-bold">
                        <i class="fas fa-lock mr-2"></i>Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-6 text-center">
            <a href="?route=profile" class="text-gold hover:text-gold-light font-rajdhani">
                <i class="fas fa-arrow-left mr-1"></i>Volver a Mi Perfil
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
