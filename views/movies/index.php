<?php
$pageTitle = 'Películas';

$filter = $_GET['filter'] ?? 'all';
$genreSlug = $_GET['genre'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 24;
$offset = ($page - 1) * $limit;

if (!empty($genreSlug)) {
    $genre = $genreModel->findBySlug($genreSlug);
    if ($genre) {
        $movies = $movieModel->getByGenre($genre['id'], $limit, $offset);
        $pageTitle = 'Géneros: ' . $genre['name'];
    } else {
        $movies = [];
    }
} else {
    switch ($filter) {
        case 'free':
            $movies = $movieModel->getFree($limit);
            $pageTitle = 'Películas Gratuitas';
            break;
        case 'paid':
            $movies = $movieModel->getPaid($limit);
            $pageTitle = 'Películas de Renta';
            break;
        case 'popular':
            $movies = $movieModel->getPopular($limit);
            $pageTitle = 'Más Populares';
            break;
        default:
            $movies = $movieModel->getAll($limit, $offset);
            $pageTitle = 'Todas las Películas';
    }
}

$genres = $genreModel->getAll();

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
        <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-2"><?= $pageTitle ?></h1>
        <p class="text-gray-400 font-rajdhani">Explora nuestro catálogo completo</p>
    </div>
    
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="?route=movies" class="genre-chip px-4 py-2 rounded-full text-sm font-rajdhani <?= $filter === 'all' ? 'bg-gold/20 border-gold text-gold' : 'text-gray-400' ?>">
            Todas
        </a>
        <a href="?route=movies&filter=free" class="genre-chip px-4 py-2 rounded-full text-sm font-rajdhani <?= $filter === 'free' ? 'bg-gold/20 border-gold text-gold' : 'text-gray-400' ?>">
            <i class="fas fa-play mr-1"></i>Gratuitas
        </a>
        <a href="?route=movies&filter=paid" class="genre-chip px-4 py-2 rounded-full text-sm font-rajdhani <?= $filter === 'paid' ? 'bg-gold/20 border-gold text-gold' : 'text-gray-400' ?>">
            <i class="fas fa-tag mr-1"></i>Renta
        </a>
        <a href="?route=movies&filter=popular" class="genre-chip px-4 py-2 rounded-full text-sm font-rajdhani <?= $filter === 'popular' ? 'bg-gold/20 border-gold text-gold' : 'text-gray-400' ?>">
            <i class="fas fa-fire mr-1"></i>Populares
        </a>
        
        <?php foreach ($genres as $genre): ?>
            <a href="?route=genre/<?= $genre['slug'] ?>" 
               class="genre-chip px-4 py-2 rounded-full text-sm font-rajdhani text-gray-400">
                <?= htmlspecialchars($genre['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($movies)): ?>
        <div class="text-center py-20">
            <i class="fas fa-film text-6xl text-gray-600 mb-4"></i>
            <h3 class="font-orbitron text-xl text-gray-400">No se encontraron películas</h3>
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
                    <div class="absolute bottom-2 left-2">
                        <span class="px-2 py-1 bg-black/70 rounded text-xs font-rajdhani text-white">
                            <i class="fas fa-eye mr-1"></i><?= $movie['views'] ?>
                        </span>
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
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
