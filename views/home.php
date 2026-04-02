<?php
$pageTitle = 'Inicio';

$latestMovies = $movieModel->getLatest(10);
$freeMovies = $movieModel->getFree(12);
$popularMovies = $movieModel->getPopular(10);
$genres = $genreModel->getAll();

function getPosterUrl($poster) {
    if (empty($poster)) {
        return 'https://via.placeholder.com/300x450/1A1A1A/D4AF37?text=GSFilms';
    }
    return $poster;
}

function getBackdropUrl($backdrop) {
    if (empty($backdrop)) {
        return 'https://via.placeholder.com/1920x600/1A1A1A/D4AF37?text=GSFilms';
    }
    return $backdrop;
}

include __DIR__ . '/layouts/header.php';
?>

<?php if (!empty($latestMovies)): ?>
<?php $heroMovie = $latestMovies[0]; ?>
<div class="relative h-[70vh] min-h-[500px] overflow-hidden">
    <img src="<?= getBackdropUrl($heroMovie['backdrop']) ?>" 
         alt="<?= htmlspecialchars($heroMovie['title']) ?>"
         class="w-full h-full object-cover">
    
    <div class="absolute inset-0 bg-gradient-to-r from-black via-black/70 to-transparent"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
    
    <div class="absolute bottom-0 left-0 right-0 p-8 md:p-16 max-w-7xl mx-auto">
        <div class="max-w-2xl">
            <span class="inline-block px-3 py-1 bg-gold/20 border border-gold/50 rounded-full text-gold font-rajdhani text-sm mb-4">
                <i class="fas fa-star mr-1"></i>Estreno
            </span>
            
            <h1 class="font-orbitron text-4xl md:text-6xl font-bold text-white mb-4">
                <?= htmlspecialchars($heroMovie['title']) ?>
            </h1>
            
            <div class="flex items-center space-x-4 text-gray-300 font-rajdhani mb-4">
                <span><i class="fas fa-calendar mr-1"></i><?= $heroMovie['release_year'] ?></span>
                <span class="w-1 h-1 bg-gold rounded-full"></span>
                <span><i class="fas fa-clock mr-1"></i><?= $heroMovie['duration'] ?> min</span>
                <span class="w-1 h-1 bg-gold rounded-full"></span>
                <?php if ($heroMovie['is_free']): ?>
                    <span class="px-2 py-0.5 bg-green-500/20 border border-green-500/50 rounded text-green-400 text-sm">
                        <i class="fas fa-play mr-1"></i>Gratis
                    </span>
                <?php else: ?>
                    <span class="px-2 py-0.5 bg-gold/20 border border-gold/50 rounded text-gold text-sm">
                        <i class="fas fa-tag mr-1"></i>$<?= number_format($heroMovie['rental_price'], 2) ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <p class="text-gray-300 font-rajdhani text-lg mb-6 line-clamp-3">
                <?= htmlspecialchars($heroMovie['synopsis'] ?? 'Sin descripción disponible.') ?>
            </p>
            
            <div class="flex flex-wrap gap-2 mb-6">
                <?php 
                $movieGenres = $movieModel->getGenres($heroMovie['id']);
                foreach (array_slice($movieGenres, 0, 3) as $genre): 
                ?>
                    <span class="genre-chip px-3 py-1 rounded-full text-sm font-rajdhani text-gold">
                        <?= htmlspecialchars($genre['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            
            <div class="flex space-x-4">
                <a href="?route=movie/<?= $heroMovie['slug'] ?>" 
                   class="hud-button px-8 py-3 rounded-lg font-rajdhani font-bold text-lg">
                    <i class="fas fa-play mr-2"></i>Ver Ahora
                </a>
                <?php if (!empty($heroMovie['trailer_url'])): ?>
                    <a href="<?= $heroMovie['trailer_url'] ?>" target="_blank"
                       class="px-8 py-3 rounded-lg font-rajdhani font-bold text-lg border border-gold/50 text-gold hover:bg-gold/10 transition">
                        <i class="fas fa-video mr-2"></i>Tráiler
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if (!empty($freeMovies)): ?>
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-orbitron text-2xl font-bold text-gold-gradient flex items-center">
                <i class="fas fa-gem mr-3"></i>Películas Gratuitas
            </h2>
            <a href="?route=movies&filter=free" class="text-gold hover:text-gold-light font-rajdhani">
                Ver todas <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach (array_slice($freeMovies, 0, 6) as $movie): ?>
            <a href="?route=movie/<?= $movie['slug'] ?>" class="movie-card block hud-container rounded-xl overflow-hidden">
                <div class="relative aspect-[2/3]">
                    <img src="<?= getPosterUrl($movie['poster']) ?>" 
                         alt="<?= htmlspecialchars($movie['title']) ?>"
                         class="w-full h-full object-cover">
                    <div class="poster-overlay absolute inset-0"></div>
                    <div class="absolute top-2 right-2">
                        <?php if ($movie['is_free']): ?>
                            <span class="px-2 py-1 bg-green-500/80 rounded text-xs font-rajdhani">
                                <i class="fas fa-play mr-1"></i>GRATIS
                            </span>
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
    </section>
    <?php endif; ?>
    
    <?php if (!empty($popularMovies)): ?>
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-orbitron text-2xl font-bold text-gold-gradient flex items-center">
                <i class="fas fa-fire mr-3"></i>Más Populares
            </h2>
            <a href="?route=movies&filter=popular" class="text-gold hover:text-gold-light font-rajdhani">
                Ver todas <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <?php foreach ($popularMovies as $movie): ?>
            <a href="?route=movie/<?= $movie['slug'] ?>" class="movie-card block hud-container rounded-xl overflow-hidden">
                <div class="relative aspect-[2/3]">
                    <img src="<?= getPosterUrl($movie['poster']) ?>" 
                         alt="<?= htmlspecialchars($movie['title']) ?>"
                         class="w-full h-full object-cover">
                    <div class="poster-overlay absolute inset-0"></div>
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-1 bg-gold/80 rounded text-xs font-rajdhani text-black font-bold">
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
    </section>
    <?php endif; ?>
    
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-orbitron text-2xl font-bold text-gold-gradient flex items-center">
                <i class="fas fa-tags mr-3"></i>Géneros
            </h2>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <?php foreach ($genres as $genre): ?>
            <a href="?route=genre/<?= $genre['slug'] ?>" 
               class="hud-container genre-chip rounded-xl p-6 text-center hover:scale-105 transition">
                <i class="fas fa-film text-3xl text-gold mb-3"></i>
                <h3 class="font-orbitron font-bold text-white"><?= htmlspecialchars($genre['name']) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    
    <?php 
    $paidMovies = $movieModel->getPaid(6);
    if (!empty($paidMovies)): 
    ?>
    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-orbitron text-2xl font-bold text-gold-gradient flex items-center">
                <i class="fas fa-crown mr-3"></i>Películas de Renta
            </h2>
            <a href="?route=movies&filter=paid" class="text-gold hover:text-gold-light font-rajdhani">
                Ver todas <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($paidMovies as $movie): ?>
            <a href="?route=movie/<?= $movie['slug'] ?>" class="movie-card block hud-container rounded-xl overflow-hidden">
                <div class="relative aspect-[2/3]">
                    <img src="<?= getPosterUrl($movie['poster']) ?>" 
                         alt="<?= htmlspecialchars($movie['title']) ?>"
                         class="w-full h-full object-cover">
                    <div class="poster-overlay absolute inset-0"></div>
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 bg-gold/80 rounded text-xs font-rajdhani text-black font-bold">
                            <i class="fas fa-tag mr-1"></i>$<?= number_format($movie['rental_price'], 2) ?>
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
    </section>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>
