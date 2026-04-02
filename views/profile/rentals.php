<?php
$pageTitle = 'Mis Rentas';

if (!isLoggedIn()) {
    header('Location: ?route=login');
    exit;
}

$userRentals = $rentalModel->getUserRentals($_SESSION['user_id'], false);

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-clock mr-3"></i>Mis Rentas
    </h1>
    
    <?php if (empty($userRentals)): ?>
        <div class="hud-container rounded-xl p-8 text-center">
            <i class="fas fa-film text-6xl text-gray-600 mb-4"></i>
            <h3 class="font-orbitron text-xl text-gray-400">No tienes rentals</h3>
            <p class="text-gray-500 mt-2">Alquila películas para verlas aquí</p>
            <a href="?route=movies" class="hud-button inline-block mt-6 px-6 py-3 rounded-lg font-rajdhani">
                Ver Películas
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($userRentals as $rental): 
                $isExpired = strtotime($rental['expires_at']) < time();
            ?>
            <div class="movie-card block hud-container rounded-xl overflow-hidden <?= $isExpired ? 'opacity-50' : '' ?>">
                <div class="relative aspect-[2/3]">
                    <img src="<?= $rental['poster'] ?: 'https://via.placeholder.com/300x450/1A1A1A/D4AF37?text=GS' ?>" 
                         alt="<?= htmlspecialchars($rental['title']) ?>"
                         class="w-full h-full object-cover">
                    <div class="poster-overlay absolute inset-0"></div>
                    <?php if ($isExpired): ?>
                        <div class="absolute inset-0 bg-black/70 flex items-center justify-center">
                            <span class="text-red-500 font-orbitron font-bold">EXPIRADO</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-3">
                    <h3 class="font-rajdhani font-semibold text-white truncate"><?= htmlspecialchars($rental['title']) ?></h3>
                    <div class="text-gray-400 text-sm mt-1">
                        <?php if ($isExpired): ?>
                            <span class="text-red-400">Expiró: <?= date('d/m/Y', strtotime($rental['expires_at'])) ?></span>
                        <?php else: ?>
                            <span class="text-green-400">Expira: <?= date('d/m/Y H:i', strtotime($rental['expires_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
