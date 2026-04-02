<?php
$pageTitle = 'Pago Cancelado';

if (!isLoggedIn()) {
    header('Location: ?route=login');
    exit;
}

$movieId = $_GET['movie_id'] ?? null;

if (!$movieId) {
    header('Location: ?route=movies');
    exit;
}

$movie = $movieModel->findById($movieId);

if (!$movie) {
    header('Location: ?route=movies');
    exit;
}

$_SESSION['stripe_session_id'] = null;
$_SESSION['rental_movie_id'] = null;

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="max-w-2xl mx-auto text-center">
        <div class="hud-container rounded-xl p-8">
            <div class="w-20 h-20 mx-auto rounded-full bg-yellow-500/20 flex items-center justify-center mb-6">
                <i class="fas fa-exclamation-triangle text-4xl text-yellow-400"></i>
            </div>
            
            <h1 class="font-orbitron text-3xl font-bold text-yellow-400 mb-4">
                Pago Cancelado
            </h1>
            
            <p class="text-gray-300 font-rajdhani mb-6">
                El proceso de pago fue cancelado. No se ha realizado ningún cargo a tu tarjeta.
            </p>
            
            <a href="?route=movie/<?= $movie['slug'] ?>" class="hud-button inline-block px-8 py-3 rounded-lg font-rajdhani font-bold">
                <i class="fas fa-arrow-left mr-2"></i>Intentar de nuevo
            </a>
            
            <div class="mt-6">
                <a href="?route=movies" class="text-gold hover:text-gold-light font-rajdhani">
                    Ver más películas <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
