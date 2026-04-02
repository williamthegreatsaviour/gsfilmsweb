<?php
$pageTitle = 'Gestionar Anuncios';

if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$ads = $adModel->getAll();

include __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="flex justify-between items-center mb-8">
        <h1 class="font-orbitron text-3xl font-bold text-gold-gradient">
            <i class="fas fa-ad mr-3"></i>Gestionar Anuncios
        </h1>
        <a href="?route=admin/ads/create" class="hud-button px-6 py-3 rounded-lg font-rajdhani">
            <i class="fas fa-plus mr-2"></i>Agregar Anuncio
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($ads as $ad): ?>
        <div class="hud-container rounded-xl overflow-hidden">
            <div class="aspect-video bg-black-light flex items-center justify-center relative">
                <i class="fas fa-play-circle text-4xl text-gold"></i>
                <?php if (!$ad['is_active']): ?>
                <span class="absolute top-2 right-2 px-2 py-1 bg-red-500/80 text-white text-xs rounded">Inactivo</span>
                <?php endif; ?>
            </div>
            <div class="p-4">
                <h3 class="font-rajdhani font-bold text-white"><?= htmlspecialchars($ad['title']) ?></h3>
                <div class="flex justify-between items-center mt-3">
                    <span class="px-2 py-1 bg-gold/20 text-gold rounded text-sm">
                        <?= ucfirst($ad['ad_type']) ?>
                    </span>
                    <span class="text-gray-400 text-sm"><?= $ad['duration'] ?>s</span>
                </div>
                <div class="flex justify-end mt-3 space-x-2">
                    <a href="?route=admin/ads/delete/<?= $ad['id'] ?>" 
                       class="text-red-400 hover:text-red-300 text-sm"
                       onclick="return confirm('¿Eliminar este anuncio?');">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($ads)): ?>
        <div class="col-span-full text-center py-12">
            <i class="fas fa-ad text-6xl text-gray-600 mb-4"></i>
            <h3 class="font-orbitron text-xl text-gray-400">No hay anuncios</h3>
            <p class="text-gray-500 mt-2">Crea anuncios para mostrar en las películas gratis</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
