<?php
$pageTitle = 'Mis Favoritos';

if (!isLoggedIn()) {
    header('Location: ?route=login');
    exit;
}

$favorites = $favoriteModel->getUserFavorites($_SESSION['user_id']);

function getPosterUrl($poster) {
    if (empty($poster)) {
        return 'https://via.placeholder.com/300x450/1A1A1A/D4AF37?text=GSFilms';
    }
    return $poster;
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-heart mr-3"></i>Mis Favoritos
    </h1>
    
    <?php if (empty($favorites)): ?>
        <div class="hud-container rounded-xl p-8 text-center">
            <i class="fas fa-heart text-6xl text-gray-600 mb-4"></i>
            <h3 class="font-orbitron text-xl text-gray-400">No tienes favoritos</h3>
            <p class="text-gray-500 mt-2">Agrega películas a tus favoritos para verlas aquí</p>
            <a href="?route=movies" class="hud-button inline-block mt-6 px-6 py-3 rounded-lg font-rajdhani">
                Ver Películas
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($favorites as $favorite): ?>
            <?php 
            $movie = $movieModel->findById($favorite['movie_id']);
            if (!$movie) continue;
            ?>
            <div class="movie-card block hud-container rounded-xl overflow-hidden relative">
                <form method="POST" action="?route=movie/<?= $movie['slug'] ?>" class="absolute top-2 right-2 z-10">
                    <input type="hidden" name="action" value="toggle_favorite">
                    <button type="submit" class="w-8 h-8 rounded-full bg-red-500/80 hover:bg-red-500 flex items-center justify-center text-white">
                        <i class="fas fa-heart"></i>
                    </button>
                </form>
                <a href="?route=movie/<?= $movie['slug'] ?>">
                    <div class="relative aspect-[2/3]">
                        <img src="<?= getPosterUrl($movie['poster']) ?>" 
                             alt="<?= htmlspecialchars($movie['title']) ?>"
                             class="w-full h-full object-cover">
                        <div class="poster-overlay absolute inset-0"></div>
                        <?php if ($movie['is_free']): ?>
                        <div class="absolute top-2 left-2">
                            <span class="px-2 py-1 bg-green-500/80 rounded text-xs font-rajdhani">
                                <i class="fas fa-play mr-1"></i>GRATIS
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <h3 class="font-rajdhani font-semibold text-white truncate"><?= htmlspecialchars($movie['title']) ?></h3>
                        <div class="flex items-center justify-between text-gray-400 text-sm mt-1">
                            <span><?= $movie['release_year'] ?></span>
                            <span><?= $movie['duration'] ?> min</span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
