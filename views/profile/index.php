<?php
$pageTitle = 'Mi Perfil';

if (!isLoggedIn()) {
    header('Location: ?route=login');
    exit;
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-user mr-3"></i>Mi Perfil
    </h1>
    
    <div class="hud-container rounded-xl p-8">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gold to-gold-dark flex items-center justify-center mr-6">
                    <span class="text-4xl font-orbitron text-black font-bold">
                        <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                    </span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($currentUser['name']) ?></h2>
                    <p class="text-gray-400"><?= htmlspecialchars($currentUser['email']) ?></p>
                    <span class="inline-block mt-2 px-3 py-1 bg-gold/20 border border-gold/50 rounded-full text-gold">
                        <?= ucfirst($currentUser['role']) ?>
                    </span>
                </div>
            </div>
            <a href="?route=profile/edit" class="hud-button px-4 py-2 rounded-lg font-rajdhani">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="?route=rentals" class="hud-container genre-chip rounded-xl p-6 text-center hover:scale-105 transition">
                <i class="fas fa-clock text-3xl text-gold mb-3"></i>
                <h3 class="font-orbitron font-bold text-white">Mis Rentas</h3>
                <p class="text-gray-400 mt-2">Películas alquiladas</p>
            </a>
            
            <a href="?route=favorites" class="hud-container genre-chip rounded-xl p-6 text-center hover:scale-105 transition">
                <i class="fas fa-heart text-3xl text-gold mb-3"></i>
                <h3 class="font-orbitron font-bold text-white">Mis Favoritos</h3>
                <p class="text-gray-400 mt-2">Películas guardadas</p>
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
