<?php
$slug = $routes[1] ?? '';
$movie = $movieModel->getFullMovie($slug);

if (!$movie) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Película no encontrada',
            text: 'La película que buscas no existe',
            background: '#1A1A1A',
            color: '#FFFFFF'
        }).then(() => {
            window.location.href = '?route=home';
        });
    </script>";
    include __DIR__ . '/../layouts/footer.php';
    exit;
}

$movieModel->incrementViews($movie['id']);
$isFavorite = isLoggedIn() ? $favoriteModel->isFavorite($_SESSION['user_id'], $movie['id']) : false;
$canWatch = false;
$hasRental = false;

if (isLoggedIn()) {
    if ($movie['is_free']) {
        $canWatch = true;
    } else {
        $hasRental = $rentalModel->hasActiveRental($_SESSION['user_id'], $movie['id']);
        if ($hasRental) {
            $canWatch = true;
        }
    }
}

$pageTitle = $movie['title'];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_favorite' && isLoggedIn()) {
        if ($isFavorite) {
            $favoriteModel->remove($_SESSION['user_id'], $movie['id']);
            $isFavorite = false;
        } else {
            $favoriteModel->add($_SESSION['user_id'], $movie['id']);
            $isFavorite = true;
        }
    }
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="relative h-[60vh] min-h-[400px]">
    <img src="<?= getBackdropUrl($movie['backdrop']) ?>" 
         alt="<?= htmlspecialchars($movie['title']) ?>"
         class="w-full h-full object-cover">
    
    <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-transparent"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
    
    <div class="absolute bottom-0 left-0 right-0 p-8 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-8">
            <div class="flex-shrink-0">
                <img src="<?= getPosterUrl($movie['poster']) ?>" 
                     alt="<?= htmlspecialchars($movie['title']) ?>"
                     class="w-48 md:w-64 rounded-xl shadow-glow">
            </div>
            
            <div class="flex-1">
                <h1 class="font-orbitron text-3xl md:text-5xl font-bold text-white mb-4">
                    <?= htmlspecialchars($movie['title']) ?>
                </h1>
                
                <div class="flex flex-wrap items-center gap-4 text-gray-300 font-rajdhani mb-4">
                    <span class="px-3 py-1 bg-gold/20 border border-gold/50 rounded-full text-gold">
                        <i class="fas fa-calendar mr-1"></i><?= $movie['release_year'] ?>
                    </span>
                    <span class="px-3 py-1 bg-gold/20 border border-gold/50 rounded-full text-gold">
                        <i class="fas fa-clock mr-1"></i><?= $movie['duration'] ?> min
                    </span>
                    <?php if ($movie['rating'] > 0): ?>
                    <span class="px-3 py-1 bg-gold/20 border border-gold/50 rounded-full text-gold">
                        <i class="fas fa-star mr-1"></i><?= number_format($movie['rating'], 1) ?>
                    </span>
                    <?php endif; ?>
                    <span class="px-3 py-1 <?= $movie['is_free'] ? 'bg-green-500/20 border border-green-500/50 text-green-400' : 'bg-gold/20 border border-gold/50 text-gold' ?> rounded-full">
                        <i class="fas <?= $movie['is_free'] ? 'fa-play' : 'fa-tag' ?> mr-1"></i>
                        <?= $movie['is_free'] ? 'Gratis' : '$' . number_format($movie['rental_price'], 2) ?>
                    </span>
                </div>
                
                <div class="flex flex-wrap gap-2 mb-6">
                    <?php foreach ($movie['genres'] as $genre): ?>
                        <a href="?route=genre/<?= $genre['slug'] ?>" 
                           class="genre-chip px-4 py-1.5 rounded-full text-sm font-rajdhani text-gold">
                            <?= htmlspecialchars($genre['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <p class="text-gray-300 font-rajdhani text-lg mb-6 max-w-2xl">
                    <?= htmlspecialchars($movie['synopsis'] ?? 'Sin descripción disponible.') ?>
                </p>
                
                <div class="flex flex-wrap gap-4">
                    <?php if ($canWatch): ?>
                        <a href="?route=player/<?= $movie['slug'] ?>" 
                           class="hud-button px-8 py-3 rounded-lg font-rajdhani font-bold text-lg">
                            <i class="fas fa-play mr-2"></i>Ver Ahora
                        </a>
                    <?php elseif (isLoggedIn() && !$movie['is_free']): ?>
                        <button onclick="rentMovie(<?= $movie['id'] ?>, '<?= $movie['slug'] ?>')"
                                class="hud-button px-8 py-3 rounded-lg font-rajdhani font-bold text-lg">
                            <i class="fas fa-shopping-cart mr-2"></i>Alquilar por $<?= number_format($movie['rental_price'], 2) ?>
                        </button>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="?route=login" 
                           class="hud-button px-8 py-3 rounded-lg font-rajdhani font-bold text-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login para Ver
                        </a>
                    <?php endif; ?>
                    
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="toggle_favorite">
                        <button type="submit" 
                                class="px-6 py-3 rounded-lg font-rajdhani font-bold text-lg border <?= $isFavorite ? 'bg-red-500/20 border-red-500 text-red-400' : 'border-gold/50 text-gold' ?> hover:bg-gold/10 transition">
                            <i class="fas fa-heart mr-2"></i><?= $isFavorite ? 'Favorito' : 'Agregar a Favoritos' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if (!empty($movie['cast'])): ?>
    <section class="mb-12">
        <h2 class="font-orbitron text-2xl font-bold text-gold-gradient mb-6">
            <i class="fas fa-users mr-3"></i>Elenco
        </h2>
        
        <div class="flex overflow-x-auto gap-4 pb-4 scrollbar-thin">
            <?php foreach ($movie['cast'] as $cast): ?>
            <div class="flex-shrink-0 w-32 text-center">
                <div class="w-24 h-24 mx-auto rounded-full bg-black-light overflow-hidden mb-2 border-2 border-gold/30">
                    <?php if (!empty($cast['image'])): ?>
                        <img src="<?= $cast['image'] ?>" alt="<?= htmlspecialchars($cast['name']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gold">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h4 class="font-rajdhani font-semibold text-white text-sm"><?= htmlspecialchars($cast['name']) ?></h4>
                <p class="text-gray-400 text-xs"><?= htmlspecialchars($cast['role']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if (!empty($movie['audio_tracks']) || !empty($movie['subtitles'])): ?>
    <section class="mb-12">
        <h2 class="font-orbitron text-2xl font-bold text-gold-gradient mb-6">
            <i class="fas fa-language mr-3"></i>Idiomas Disponibles
        </h2>
        
        <div class="hud-container rounded-xl p-6">
            <?php if (!empty($movie['audio_tracks'])): ?>
            <div class="mb-6">
                <h3 class="text-gold font-rajdhani font-bold mb-3">Pistas de Audio</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($movie['audio_tracks'] as $track): ?>
                        <span class="px-4 py-2 bg-black-light rounded-lg text-white font-rajdhani <?= $track['is_default'] ? 'border border-gold' : '' ?>">
                            <i class="fas fa-volume-up mr-2 text-gold"></i><?= htmlspecialchars($track['label']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($movie['subtitles'])): ?>
            <div>
                <h3 class="text-gold font-rajdhani font-bold mb-3">Subtítulos</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($movie['subtitles'] as $sub): ?>
                        <span class="px-4 py-2 bg-black-light rounded-lg text-white font-rajdhani <?= $sub['is_default'] ? 'border border-gold' : '' ?>">
                            <i class="fas fa-closed-captioning mr-2 text-gold"></i><?= htmlspecialchars($sub['label']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <?php 
    $relatedMovies = $movieModel->getByGenre($movie['genres'][0]['id'] ?? 0, 6);
    $relatedMovies = array_filter($relatedMovies, fn($m) => $m['id'] !== $movie['id']);
    if (!empty($relatedMovies)): 
    ?>
    <section>
        <h2 class="font-orbitron text-2xl font-bold text-gold-gradient mb-6">
            <i class="fas fa-film mr-3"></i>Películas Relacionadas
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach (array_slice($relatedMovies, 0, 6) as $related): ?>
            <a href="?route=movie/<?= $related['slug'] ?>" class="movie-card block hud-container rounded-xl overflow-hidden">
                <div class="relative aspect-[2/3]">
                    <img src="<?= getPosterUrl($related['poster']) ?>" 
                         alt="<?= htmlspecialchars($related['title']) ?>"
                         class="w-full h-full object-cover">
                    <div class="poster-overlay absolute inset-0"></div>
                </div>
                <div class="p-3">
                    <h3 class="font-rajdhani font-semibold text-white truncate"><?= htmlspecialchars($related['title']) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
function rentMovie(movieId, slug) {
    Swal.fire({
        title: 'Alquilar Película',
        text: '¿Estás seguro de alquilar esta película por 48 horas?',
        icon: 'question',
        background: '#1A1A1A',
        color: '#FFFFFF',
        showCancelButton: true,
        confirmButtonColor: '#D4AF37',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sí, alquilar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?route=payment/create&movie_id=' + movieId;
        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
