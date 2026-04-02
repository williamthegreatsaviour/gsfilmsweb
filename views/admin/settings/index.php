<?php
$pageTitle = 'Configuración';

if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$settingsModel = new Settings();
$settings = $settingsModel->getAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'site_name' => 'Nombre del Sitio',
        'site_email' => 'Email de Contacto',
        'site_phone' => 'Teléfono',
        'site_facebook' => 'Facebook',
        'site_twitter' => 'Twitter',
        'site_instagram' => 'Instagram',
        'site_youtube' => 'YouTube',
        'stripe_secret_key' => 'Stripe Secret Key',
        'stripe_publishable_key' => 'Stripe Publishable Key',
        'stripe_webhook_secret' => 'Stripe Webhook Secret',
        'rental_duration_hours' => 'Duración del Alquiler (horas)'
    ];
    
    foreach ($fields as $key => $label) {
        if (isset($_POST[$key])) {
            $settingsModel->set($key, $_POST[$key]);
        }
    }
    
    Settings::clearCache();
    $settings = $settingsModel->getAll();
    $success = 'Configuración guardada correctamente';
}

include __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="mb-8">
        <a href="?route=admin/dashboard" class="text-gold hover:text-gold-light font-rajdhani">
            <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
        </a>
    </div>
    
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-cog mr-3"></i>Configuración del Sitio
    </h1>
    
    <?php if ($success): ?>
    <div class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-lg mb-6">
        <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" class="space-y-8">
        <div class="hud-container rounded-xl p-6">
            <h2 class="font-orbitron text-xl text-gold mb-4">
                <i class="fas fa-globe mr-2"></i>Información del Sitio
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Nombre del Sitio</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'GSFilms') ?>" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="GSFilms">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Email de Contacto</label>
                    <input type="email" name="site_email" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="contacto@gsfilms.com">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Teléfono</label>
                    <input type="text" name="site_phone" value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="+1 234 567 890">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Duración del Alquiler (horas)</label>
                    <input type="number" name="rental_duration_hours" value="<?= intval($settings['rental_duration_hours'] ?? 48) ?>" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="48" min="1">
                </div>
            </div>
        </div>
        
        <div class="hud-container rounded-xl p-6">
            <h2 class="font-orbitron text-xl text-gold mb-4">
                <i class="fab fa-facebook mr-2"></i>Redes Sociales
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Facebook</label>
                    <div class="flex items-center">
                        <span class="bg-black-light px-3 py-3 rounded-l-lg border border-r-0 border-gold/30">
                            <i class="fab fa-facebook text-gold"></i>
                        </span>
                        <input type="url" name="site_facebook" value="<?= htmlspecialchars($settings['site_facebook'] ?? '') ?>" 
                               class="hud-input w-full px-4 py-3 rounded-l-none rounded-r-lg font-rajdhani"
                               placeholder="https://facebook.com/gsfilms">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Twitter / X</label>
                    <div class="flex items-center">
                        <span class="bg-black-light px-3 py-3 rounded-l-lg border border-r-0 border-gold/30">
                            <i class="fab fa-twitter text-gold"></i>
                        </span>
                        <input type="url" name="site_twitter" value="<?= htmlspecialchars($settings['site_twitter'] ?? '') ?>" 
                               class="hud-input w-full px-4 py-3 rounded-l-none rounded-r-lg font-rajdhani"
                               placeholder="https://twitter.com/gsfilms">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Instagram</label>
                    <div class="flex items-center">
                        <span class="bg-black-light px-3 py-3 rounded-l-lg border border-r-0 border-gold/30">
                            <i class="fab fa-instagram text-gold"></i>
                        </span>
                        <input type="url" name="site_instagram" value="<?= htmlspecialchars($settings['site_instagram'] ?? '') ?>" 
                               class="hud-input w-full px-4 py-3 rounded-l-none rounded-r-lg font-rajdhani"
                               placeholder="https://instagram.com/gsfilms">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">YouTube</label>
                    <div class="flex items-center">
                        <span class="bg-black-light px-3 py-3 rounded-l-lg border border-r-0 border-gold/30">
                            <i class="fab fa-youtube text-gold"></i>
                        </span>
                        <input type="url" name="site_youtube" value="<?= htmlspecialchars($settings['site_youtube'] ?? '') ?>" 
                               class="hud-input w-full px-4 py-3 rounded-l-none rounded-r-lg font-rajdhani"
                               placeholder="https://youtube.com/@gsfilms">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="hud-container rounded-xl p-6">
            <h2 class="font-orbitron text-xl text-gold mb-4">
                <i class="fab fa-stripe mr-2"></i>Configuración de Pagos (Stripe)
            </h2>
            <p class="text-gray-400 text-sm mb-4">
                <i class="fas fa-info-circle mr-1"></i>
                Deja las claves vacías para usar el modo demo (los rentals se crean sin costo)
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Stripe Secret Key</label>
                    <input type="password" name="stripe_secret_key" value="<?= htmlspecialchars($settings['stripe_secret_key'] ?? '') ?>" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="sk_test_...">
                    <p class="text-gray-500 text-xs mt-1">Comienza con sk_</p>
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Stripe Publishable Key</label>
                    <input type="password" name="stripe_publishable_key" value="<?= htmlspecialchars($settings['stripe_publishable_key'] ?? '') ?>" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="pk_test_...">
                    <p class="text-gray-500 text-xs mt-1">Comienza con pk_</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-gold font-rajdhani mb-2">Stripe Webhook Secret</label>
                    <input type="password" name="stripe_webhook_secret" value="<?= htmlspecialchars($settings['stripe_webhook_secret'] ?? '') ?>" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="whsec_...">
                    <p class="text-gray-500 text-xs mt-1">Necesario para recibir notificaciones de pagos (opcional)</p>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="hud-button px-8 py-3 rounded-lg font-rajdhani font-bold">
                <i class="fas fa-save mr-2"></i>Guardar Configuración
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
