<?php
$pageTitle = 'Pago Exitoso';

if (!isLoggedIn()) {
    header('Location: ?route=login');
    exit;
}

$sessionId = $_GET['session_id'] ?? null;
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

$stripeConfigured = \Config::isStripeConfigured();
$rentalHours = \Config::getRentalDurationHours();
$paymentSuccess = false;
$error = '';

if ($sessionId === 'demo' || !$stripeConfigured) {
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $rentalHours . ' hours'));
    
    $existingRental = $rentalModel->hasActiveRental($_SESSION['user_id'], $movieId);
    
    if (!$existingRental) {
        $rentalId = $rentalModel->create([
            'user_id' => $_SESSION['user_id'],
            'movie_id' => $movieId,
            'rental_price' => $movie['rental_price'],
            'expires_at' => $expiresAt,
            'stripe_payment_intent' => $sessionId ?? 'demo',
            'is_active' => 1
        ]);
    }
    
    $paymentSuccess = true;
} elseif ($stripeConfigured && $sessionId) {
    try {
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
        \Stripe\Stripe::setApiKey(\Config::getStripeSecretKey());
        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        
        if ($session->payment_status === 'paid') {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $rentalHours . ' hours'));
            
            $existingRental = $rentalModel->hasActiveRental($_SESSION['user_id'], $movieId);
            
            if (!$existingRental) {
                $rentalId = $rentalModel->create([
                    'user_id' => $_SESSION['user_id'],
                    'movie_id' => $movieId,
                    'rental_price' => $movie['rental_price'],
                    'expires_at' => $expiresAt,
                    'stripe_payment_intent' => $session->payment_intent,
                    'is_active' => 1
                ]);
            }
            
            $paymentSuccess = true;
        }
    } catch (Exception $e) {
        $error = 'Error al verificar el pago: ' . $e->getMessage();
    }
}

$_SESSION['stripe_session_id'] = null;
$_SESSION['rental_movie_id'] = null;

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="max-w-2xl mx-auto text-center">
        <?php if ($paymentSuccess): ?>
        <div class="hud-container rounded-xl p-8">
            <div class="w-20 h-20 mx-auto rounded-full bg-green-500/20 flex items-center justify-center mb-6">
                <i class="fas fa-check text-4xl text-green-400"></i>
            </div>
            
            <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-4">
                ¡Alquiler Exitoso!
            </h1>
            
            <p class="text-gray-300 font-rajdhani text-lg mb-6">
                Has alquilado <strong class="text-white"><?= htmlspecialchars($movie['title']) ?></strong>
            </p>
            
            <div class="bg-black-light rounded-lg p-4 mb-6">
                <p class="text-gray-400 text-sm">Tu alquiler expira el</p>
                <p class="text-gold font-orbitron text-xl"><?= date('d/m/Y H:i', strtotime('+' . $rentalHours . ' hours')) ?></p>
            </div>
            
            <a href="?route=player/<?= $movie['slug'] ?>" class="hud-button inline-block px-8 py-3 rounded-lg font-rajdhani font-bold">
                <i class="fas fa-play mr-2"></i>Ver Ahora
            </a>
            
            <div class="mt-6">
                <a href="?route=rentals" class="text-gold hover:text-gold-light font-rajdhani">
                    Ver mis rentals <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="hud-container rounded-xl p-8">
            <div class="w-20 h-20 mx-auto rounded-full bg-red-500/20 flex items-center justify-center mb-6">
                <i class="fas fa-times text-4xl text-red-400"></i>
            </div>
            
            <h1 class="font-orbitron text-3xl font-bold text-red-400 mb-4">
                Error en el Pago
            </h1>
            
            <p class="text-gray-300 font-rajdhani mb-6">
                <?= htmlspecialchars($error) ?>
            </p>
            
            <a href="?route=movie/<?= $movie['slug'] ?>" class="hud-button inline-block px-8 py-3 rounded-lg font-rajdhani font-bold">
                <i class="fas fa-arrow-left mr-2"></i>Volver a la película
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
