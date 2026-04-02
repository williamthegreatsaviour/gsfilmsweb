<?php
$pageTitle = 'Crear Anuncio';

if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    $thumbnail = $_POST['thumbnail'] ?? '';
    $duration = intval($_POST['duration'] ?? 30);
    $ad_type = $_POST['ad_type'] ?? 'preroll';
    $target_url = $_POST['target_url'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($title) || empty($video_url)) {
        $error = 'El título y la URL del video son obligatorios';
    } else {
        $adId = $adModel->create([
            'title' => $title,
            'description' => $description,
            'video_url' => $video_url,
            'thumbnail' => $thumbnail,
            'duration' => $duration,
            'ad_type' => $ad_type,
            'target_url' => $target_url,
            'is_active' => $is_active,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);
        
        $success = 'Anuncio creado correctamente';
    }
}

include __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="mb-8">
        <a href="?route=admin/ads" class="text-gold hover:text-gold-light font-rajdhani">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Anuncios
        </a>
    </div>
    
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-plus mr-3"></i>Crear Anuncio
    </h1>
    
    <?php if ($error): ?>
    <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-lg mb-6">
        <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    
    <div class="hud-container rounded-xl p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Título *</label>
                    <input type="text" name="title" required 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="Nombre del anuncio">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Tipo de Anuncio</label>
                    <select name="ad_type" class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                        <option value="preroll">Pre-roll (Antes)</option>
                        <option value="midroll">Mid-roll (Durante)</option>
                        <option value="postroll">Post-roll (Después)</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-gold font-rajdhani mb-2">Descripción</label>
                <textarea name="description" rows="3" 
                          class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                          placeholder="Descripción del anuncio"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">URL del Video *</label>
                    <input type="url" name="video_url" required 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="https://...">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">URL del Thumbnail</label>
                    <input type="url" name="thumbnail" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="https://...">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Duración (segundos)</label>
                    <input type="number" name="duration" value="30" min="5" max="300" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">URL de Destino</label>
                    <input type="url" name="target_url" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="https://...">
                </div>
                
                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="sr-only">
                        <div class="w-5 h-5 border-2 border-gold rounded mr-3 flex items-center justify-center bg-black-light">
                            <i class="fas fa-check text-gold text-xs"></i>
                        </div>
                        <span class="text-gold font-rajdhani">Activo</span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="hud-button px-8 py-3 rounded-lg font-rajdhani font-bold">
                    <i class="fas fa-save mr-2"></i>Guardar Anuncio
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
