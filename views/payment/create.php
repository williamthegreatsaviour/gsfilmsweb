<?php
$pageTitle = 'Pagar Alquiler';

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

if ($movie['is_free']) {
    header('Location: ?route=movie/' . $movie['slug']);
    exit;
}

$hasRental = $rentalModel->hasActiveRental($_SESSION['user_id'], $movieId);

if ($hasRental) {
    header('Location: ?route=player/' . $movie['slug']);
    exit;
}

require_once __DIR__ . '/../../models/Payment.php';
$paymentModel = new Payment();

$error = '';
$stripeConfigured = \Config::isStripeConfigured();
$rentalHours = \Config::getRentalDurationHours();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$stripeConfigured) {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $rentalHours . ' hours'));
        
        $rentalId = $rentalModel->create([
            'user_id' => $_SESSION['user_id'],
            'movie_id' => $movieId,
            'rental_price' => $movie['rental_price'],
            'expires_at' => $expiresAt,
            'stripe_payment_intent' => 'demo_mode',
            'is_active' => 1
        ]);
        
        header('Location: ?route=payment/success&session_id=demo&movie_id=' . $movieId);
        exit;
    } else {
        $stripeSecretKey = \Config::getStripeSecretKey();
        
        if (empty($stripeSecretKey)) {
            $error = 'Stripe no está configurado correctamente. Contacta al administrador.';
        } else {
            try {
                require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
                \Stripe\Stripe::setApiKey($stripeSecretKey);
                
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Alquiler: ' . $movie['title'],
                                'description' => 'Acceso por ' . $rentalHours . ' horas',
                            ],
                            'unit_amount' => intval($movie['rental_price'] * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => \Config::getBaseUrl() . '/?route=payment/success&session_id={CHECKOUT_SESSION_ID}&movie_id=' . $movieId,
                    'cancel_url' => \Config::getBaseUrl() . '/?route=payment/cancel&movie_id=' . $movieId,
                    'metadata' => [
                        'user_id' => $_SESSION['user_id'],
                        'movie_id' => $movieId,
                        'type' => 'rental'
                    ]
                ]);
                
                $_SESSION['stripe_session_id'] = $session->id;
                $_SESSION['rental_movie_id'] = $movieId;
                
                echo "<script>window.location.href = '" . $session->url . "';</script>";
                exit;
                
            } catch (Exception $e) {
                $error = 'Error al procesar el pago: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="max-w-2xl mx-auto">
        <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8 text-center">
            <i class="fas fa-shopping-cart mr-3"></i>Confirmar Alquiler
        </h1>
        
        <?php if ($error): ?>
        <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <div class="hud-container rounded-xl p-6">
            <div class="flex gap-6 mb-6">
                <img src="<?= $movie['poster'] ?: 'https://via.placeholder.com/300x450/1A1A1A/D4AF37?text=GS' ?>" 
                     alt="<?= htmlspecialchars($movie['title']) ?>"
                     class="w-32 h-48 object-cover rounded-lg">
                
                <div>
                    <h2 class="font-orbitron text-xl text-white mb-2"><?= htmlspecialchars($movie['title']) ?></h2>
                    <p class="text-gray-400 mb-2"><?= $movie['release_year'] ?> • <?= $movie['duration'] ?> min</p>
                    <p class="text-gray-400 text-sm"><?= htmlspecialchars($movie['synopsis'] ?? '') ?></p>
                </div>
            </div>
            
            <div class="border-t border-gold/20 pt-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-400">Precio de alquiler</span>
                    <span class="text-white font-rajdhani">$<?= number_format($movie['rental_price'], 2) ?></span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-400">Duración</span>
                    <span class="text-white font-rajdhani"><?= $rentalHours ?> horas</span>
                </div>
                <div class="flex justify-between items-center text-lg font-bold">
                    <span class="text-gold">Total</span>
                    <span class="text-gold">$<?= number_format($movie['rental_price'], 2) ?></span>
                </div>
            </div>
            
            <?php if (!$stripeConfigured): ?>
            <div class="bg-yellow-500/20 border border-yellow-500 text-yellow-400 px-4 py-3 rounded-lg mb-4 text-sm">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Modo Demo:</strong> No tienes configurado Stripe. El alquiler se creará sin costo.
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <?php if ($stripeConfigured): ?>
                <button type="submit" class="hud-button w-full py-4 rounded-lg font-rajdhani font-bold text-lg">
                    <i class="fas fa-credit-card mr-2"></i>Pagar con Stripe
                </button>
                <?php else: ?>
                <button type="submit" class="hud-button w-full py-4 rounded-lg font-rajdhani font-bold text-lg">
                    <i class="fas fa-play mr-2"></i>Crear Alquiler (Demo)
                </button>
                <?php endif; ?>
            </form>
            
            <p class="text-gray-500 text-center text-sm mt-4">
                <?php if ($stripeConfigured): ?>
                <i class="fas fa-lock mr-1"></i>Pago seguro con Stripe
                <?php else: ?>
                <i class="fas fa-flask mr-1"></i>Prueba sin costo
                <?php endif; ?>
            </p>
            
            <div class="text-center mt-4">
                <a href="?route=movie/<?= $movie['slug'] ?>" class="text-gold hover:text-gold-light font-rajdhani">
                    <i class="fas fa-arrow-left mr-1"></i>Volver a la película
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
