<?php
$pageTitle = 'Buscar';

$query = $_GET['q'] ?? '';
$movies = [];

if (!empty($query)) {
    $movies = $movieModel->search($query);
}

function getPosterUrl($poster) {
    if (empty($poster)) {
        return 'https://via.placeholder.com/300x450/1A1A1A/D4AF37?text=GSFilms';
    }
    return $poster;
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="mb-8">
        <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-4">
            <i class="fas fa-search mr-3"></i>Buscar Películas
        </h1>
        
        <form method="GET" class="max-w-2xl">
            <input type="hidden" name="route" value="search">
            <div class="relative">
                <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
                       placeholder="Buscar por título, actor, género..."
                       class="hud-input w-full px-6 py-4 rounded-xl text-lg font-rajdhani">
                <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 hud-button px-6 py-2 rounded-lg">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
    
    <?php if (!empty($query)): ?>
        <p class="text-gray-400 font-rajdhani mb-6">
            <?= count($movies) ?> resultados para "<?= htmlspecialchars($query) ?>"
        </p>
        
        <?php if (empty($movies)): ?>
            <div class="text-center py-20">
                <i class="fas fa-search text-6xl text-gray-600 mb-4"></i>
                <h3 class="font-orbitron text-xl text-gray-400">No se encontraron resultados</h3>
                <p class="text-gray-500 mt-2">Intenta con otras palabras</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($movies as $movie): ?>
                <a href="?route=movie/<?= $movie['slug'] ?>" class="movie-card block hud-container rounded-xl overflow-hidden">
                    <div class="relative aspect-[2/3]">
                        <img src="<?= getPosterUrl($movie['poster']) ?>" 
                             alt="<?= htmlspecialchars($movie['title']) ?>"
                             class="w-full h-full object-cover">
                        <div class="poster-overlay absolute inset-0"></div>
                        <div class="absolute top-2 right-2">
                            <?php if ($movie['is_free']): ?>
                                <span class="px-2 py-1 bg-green-500/80 rounded text-xs font-rajdhani">GRATIS</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-gold/80 rounded text-xs font-rajdhani text-black font-bold">$<?= number_format($movie['rental_price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="font-rajdhani font-semibold text-white truncate"><?= htmlspecialchars($movie['title']) ?></h3>
                        <div class="flex items-center justify-between text-gray-400 text-sm mt-1">
                            <span><?= $movie['release_year'] ?></span>
                            <span><?= $movie['duration'] ?> min</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
