<?php
if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$pageTitle = 'Panel de Admin';

$totalMovies = $db->fetchOne("SELECT COUNT(*) as count FROM movies")['count'] ?? 0;
$totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
$totalRentals = $db->fetchOne("SELECT COUNT(*) as count FROM rentals")['count'] ?? 0;
$totalAds = $db->fetchOne("SELECT COUNT(*) as count FROM ads")['count'] ?? 0;

$recentMovies = $movieModel->getLatest(5);
$recentRentals = $db->fetchAll("
    SELECT r.*, u.name as user_name, m.title as movie_title 
    FROM rentals r 
    INNER JOIN users u ON r.user_id = u.id 
    INNER JOIN movies m ON r.movie_id = m.id 
    ORDER BY r.rented_at DESC LIMIT 10
");

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-cog mr-3"></i>Panel de Administración
    </h1>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="hud-container rounded-xl p-6 text-center">
            <i class="fas fa-film text-3xl text-gold mb-2"></i>
            <div class="text-3xl font-orbitron font-bold text-white"><?= $totalMovies ?></div>
            <div class="text-gray-400 font-rajdhani">Películas</div>
        </div>
        
        <div class="hud-container rounded-xl p-6 text-center">
            <i class="fas fa-users text-3xl text-gold mb-2"></i>
            <div class="text-3xl font-orbitron font-bold text-white"><?= $totalUsers ?></div>
            <div class="text-gray-400 font-rajdhani">Usuarios</div>
        </div>
        
        <div class="hud-container rounded-xl p-6 text-center">
            <i class="fas fa-rental-car text-3xl text-gold mb-2"></i>
            <div class="text-3xl font-orbitron font-bold text-white"><?= $totalRentals ?></div>
            <div class="text-gray-400 font-rajdhani">Rentas</div>
        </div>
        
        <div class="hud-container rounded-xl p-6 text-center">
            <i class="fas fa-ad text-3xl text-gold mb-2"></i>
            <div class="text-3xl font-orbitron font-bold text-white"><?= $totalAds ?></div>
            <div class="text-gray-400 font-rajdhani">Anuncios</div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="hud-container rounded-xl p-6">
            <h2 class="font-orbitron text-xl font-bold text-gold mb-4">Últimas Películas</h2>
            <div class="space-y-3">
                <?php foreach ($recentMovies as $m): ?>
                <div class="flex items-center justify-between p-3 bg-black-light rounded-lg">
                    <div class="flex items-center gap-3">
                        <img src="<?= $m['poster'] ?: 'https://via.placeholder.com/50x75/1A1A1A/D4AF37?text=GS' ?>" 
                             class="w-12 h-16 object-cover rounded">
                        <div>
                            <div class="text-white font-rajdhani"><?= htmlspecialchars($m['title']) ?></div>
                            <div class="text-gray-500 text-sm"><?= $m['release_year'] ?></div>
                        </div>
                    </div>
                    <a href="?route=admin/movies/edit/<?= $m['id'] ?>" class="text-gold hover:text-gold-light">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="?route=admin/movies" class="block text-center text-gold mt-4 font-rajdhani">
                Ver todas las películas <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="hud-container rounded-xl p-6">
            <h2 class="font-orbitron text-xl font-bold text-gold mb-4">Últimas Rentas</h2>
            <div class="space-y-3">
                <?php foreach ($recentRentals as $r): ?>
                <div class="flex items-center justify-between p-3 bg-black-light rounded-lg">
                    <div>
                        <div class="text-white font-rajdhani"><?= htmlspecialchars($r['movie_title']) ?></div>
                        <div class="text-gray-500 text-sm"><?= htmlspecialchars($r['user_name']) ?></div>
                    </div>
                    <div class="text-right">
                        <div class="text-gold font-rajdhani">$<?= number_format($r['rental_price'], 2) ?></div>
                        <div class="text-gray-500 text-xs"><?= date('d/m/Y H:i', strtotime($r['rented_at'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-8">
        <a href="?route=admin/movies" class="hud-container genre-chip rounded-xl p-6 text-center hover:scale-105 transition">
            <i class="fas fa-film text-3xl text-gold mb-3"></i>
            <h3 class="font-orbitron font-bold text-white">Películas</h3>
        </a>
        
        <a href="?route=admin/ads" class="hud-container genre-chip rounded-xl p-6 text-center hover:scale-105 transition">
            <i class="fas fa-ad text-3xl text-gold mb-3"></i>
            <h3 class="font-orbitron font-bold text-white">Anuncios</h3>
        </a>
        
        <a href="?route=admin/users" class="hud-container genre-chip rounded-xl p-6 text-center hover:scale-105 transition">
            <i class="fas fa-users text-3xl text-gold mb-3"></i>
            <h3 class="font-orbitron font-bold text-white">Usuarios</h3>
        </a>
        
        <a href="?route=admin/genres" class="hud-container genre-chip rounded-xl p-6 text-center hover:scale-105 transition">
            <i class="fas fa-tags text-3xl text-gold mb-3"></i>
            <h3 class="font-orbitron font-bold text-white">Géneros</h3>
        </a>
        
        <a href="?route=admin/settings" class="hud-container genre-chip rounded-xl p-6 text-center hover:scale-105 transition">
            <i class="fas fa-cog text-3xl text-gold mb-3"></i>
            <h3 class="font-orbitron font-bold text-white">Config</h3>
        </a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
