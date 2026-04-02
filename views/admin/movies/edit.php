<?php
require_once dirname(__DIR__, 2) . '/config/Database.php';
require_once dirname(__DIR__, 2) . '/models/Ad.php';

$pageTitle = 'Editar Película';

if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$movieId = $routes[2] ?? null;
$movie = $movieId ? $movieModel->findById($movieId) : null;

if (!$movie) {
    header('Location: ?route=admin/movies');
    exit;
}

$error = '';
$success = '';
$genres = $genreModel->getAll();
$movieGenres = $movieModel->getGenres($movieId);
$audioTracks = $movieModel->getAudioTracks($movieId);
$subtitles = $movieModel->getSubtitles($movieId);

$adModel = new Ad();
$allAds = $adModel->getAll(false);
$movieAds = $adModel->getMovieAds($movieId);
$currentAdIds = array_column($movieAds, 'id');

function handleFileUpload($fileKey, $targetDir, $allowedTypes = []) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $file = $_FILES[$fileKey];
    $filename = uniqid() . '_' . basename($file['name']);
    $targetPath = $targetDir . $filename;
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return '/uploads/' . basename($targetDir) . '/' . $filename;
    }
    
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $synopsis = $_POST['synopsis'] ?? '';
    $duration = intval($_POST['duration'] ?? 0);
    $release_year = intval($_POST['release_year'] ?? date('Y'));
    $rating = floatval($_POST['rating'] ?? 0);
    $trailer_url = $_POST['trailer_url'] ?? '';
    $pricing_type = $_POST['pricing_type'] ?? 'free';
    $is_free = ($pricing_type === 'free') ? 1 : 0;
    $rental_price = floatval($_POST['rental_price'] ?? 0);
    $selected_genres = $_POST['genres'] ?? [];
    
    $audio_tracks = $_POST['audio_tracks'] ?? [];
    $subtitles_data = $_POST['subtitles'] ?? [];
    
    $poster = $movie['poster'] ?? '';
    $poster_type = $_POST['poster_type'] ?? 'url';
    if ($poster_type === 'url') {
        $poster = $_POST['poster_url'] ?? $poster;
    } else {
        $newPoster = handleFileUpload('poster_file', __DIR__ . '/../../../public/uploads/posters/', ['image/jpeg', 'image/png', 'image/webp']);
        if ($newPoster) $poster = $newPoster;
    }
    
    $backdrop = $movie['backdrop'] ?? '';
    $backdrop_type = $_POST['backdrop_type'] ?? 'url';
    if ($backdrop_type === 'url') {
        $backdrop = $_POST['backdrop_url'] ?? $backdrop;
    } else {
        $newBackdrop = handleFileUpload('backdrop_file', __DIR__ . '/../../../public/uploads/backdrops/', ['image/jpeg', 'image/png', 'image/webm']);
        if ($newBackdrop) $backdrop = $newBackdrop;
    }
    
    $video_source = $_POST['video_source'] ?? 'upload';
    $video_url = $movie['video_url'] ?? '';
    $video_type = $movie['video_type'] ?? 'direct';
    $youtube_id = $movie['youtube_id'] ?? '';
    
    if ($video_source === 'upload') {
        $video_type = 'direct';
        $newVideo = handleFileUpload('video_file', __DIR__ . '/../../../public/uploads/movies/', ['video/mp4', 'video/webm', 'video/x-matroska']);
        if ($newVideo) $video_url = $newVideo;
    } elseif ($video_source === 'youtube') {
        $video_type = 'youtube';
        $youtube_id = $_POST['youtube_video_id'] ?? $youtube_id;
    } elseif ($video_source === 'cdn') {
        $video_type = 'direct';
        $video_url = $_POST['video_cdn_url'] ?? $video_url;
    } elseif ($video_source === 'embed') {
        $video_type = 'embed';
        $video_url = $_POST['video_embed_code'] ?? $video_url;
    }
    $trailer_url = $_POST['trailer_url'] ?? '';
    $pricing_type = $_POST['pricing_type'] ?? 'free';
    $is_free = ($pricing_type === 'free') ? 1 : 0;
    $rental_price = floatval($_POST['rental_price'] ?? 0);
    $video_url = $_POST['video_url'] ?? '';
    $video_type = $_POST['video_type'] ?? 'direct';
    $youtube_id = $_POST['youtube_id'] ?? '';
    $selected_genres = $_POST['genres'] ?? [];
    
    $audio_tracks = $_POST['audio_tracks'] ?? [];
    $subtitles_data = $_POST['subtitles'] ?? [];
    
    if (empty($title)) {
        $error = 'El título es obligatorio';
    } else {
        $movieModel->update($movieId, [
            'title' => $title,
            'slug' => $slug,
            'synopsis' => $synopsis,
            'duration' => $duration,
            'release_year' => $release_year,
            'rating' => $rating,
            'poster' => $poster,
            'backdrop' => $backdrop,
            'trailer_url' => $trailer_url,
            'is_free' => $is_free,
            'rental_price' => $rental_price,
            'video_url' => $video_url,
            'video_type' => $video_type,
            'youtube_id' => $youtube_id
        ]);
        
        $movieModel->setGenres($movieId, $selected_genres);
        
        $db = Database::getInstance();
        $db->query("DELETE FROM audio_tracks WHERE movie_id = ?", [$movieId]);
        foreach ($audio_tracks as $track) {
            if (!empty($track['language']) && !empty($track['label'])) {
                $movieModel->addAudioTrack($movieId, [
                    'language' => $track['language'],
                    'label' => $track['label'],
                    'url' => $track['url'] ?? '',
                    'is_default' => isset($track['is_default']) ? 1 : 0
                ]);
            }
        }
        
        $db->query("DELETE FROM subtitles WHERE movie_id = ?", [$movieId]);
        foreach ($subtitles_data as $sub) {
            if (!empty($sub['language']) && !empty($sub['label'])) {
                $movieModel->addSubtitle($movieId, [
                    'language' => $sub['language'],
                    'label' => $sub['label'],
                    'vtt_url' => $sub['vtt_url'] ?? '',
                    'vtt_content' => $sub['vtt_content'] ?? '',
                    'is_default' => isset($sub['is_default']) ? 1 : 0
                ]);
            }
        }
        
        $movie = $movieModel->findById($movieId);
        $movieGenres = $movieModel->getGenres($movieId);
        $audioTracks = $movieModel->getAudioTracks($movieId);
        $subtitles = $movieModel->getSubtitles($movieId);
        
        if (isset($_POST['update_ads'])) {
            $selectedAds = $_POST['ads'] ?? [];
            $adModel->clearMovieAds($movieId);
            foreach ($selectedAds as $adId) {
                $adModel->assignToMovie($movieId, $adId, 1, 0);
            }
            $movieAds = $adModel->getMovieAds($movieId);
            $currentAdIds = array_column($movieAds, 'id');
            $success = 'Anuncios actualizados correctamente';
        } else {
            $success = 'Película actualizada correctamente';
        }
    }
}

$currentGenreIds = array_column($movieGenres, 'id');

include __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="mb-8">
        <a href="?route=admin/movies" class="text-gold hover:text-gold-light font-rajdhani">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Películas
        </a>
    </div>
    
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-edit mr-3"></i>Editar Película
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
    
    <div class="hud-container rounded-xl p-6 mb-6">
        <form method="POST" class="space-y-6">
            <h2 class="font-orbitron text-xl text-gold mb-4">Información Básica</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Título *</label>
                    <input type="text" name="title" required 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           value="<?= htmlspecialchars($movie['title']) ?>">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Año de Estreno</label>
                    <input type="number" name="release_year" value="<?= $movie['release_year'] ?>" min="1900" max="2030" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
            </div>
            
            <div>
                <label class="block text-gold font-rajdhani mb-2">Sinopsis</label>
                <textarea name="synopsis" rows="3" 
                          class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"><?= htmlspecialchars($movie['synopsis'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Duración (minutos)</label>
                    <input type="number" name="duration" value="<?= $movie['duration'] ?>" min="1" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Rating (0-10)</label>
                    <input type="number" name="rating" value="<?= $movie['rating'] ?>" min="0" max="10" step="0.1" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Póster</label>
                    <select name="poster_type" id="poster_type" class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani mb-2" onchange="togglePosterFields()">
                        <option value="url" <?= !empty($movie['poster']) && strpos($movie['poster'], '/uploads/') === false ? 'selected' : '' ?>>🔗 URL</option>
                        <option value="upload" <?= !empty($movie['poster']) && strpos($movie['poster'], '/uploads/') !== false ? 'selected' : '' ?>>📁 Subir archivo</option>
                    </select>
                    <input type="url" name="poster_url" id="poster_url" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani <?= !empty($movie['poster']) && strpos($movie['poster'], '/uploads/') !== false ? 'hidden' : '' ?>"
                           value="<?= htmlspecialchars($movie['poster'] ?? '') ?>" placeholder="https://...">
                    <input type="file" name="poster_file" id="poster_file" accept="image/*"
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani <?= empty($movie['poster']) || strpos($movie['poster'], '/uploads/') === false ? 'hidden' : '' ?>"
                           onchange="previewPoster(this)">
                    <input type="hidden" name="poster" id="poster_value" value="<?= htmlspecialchars($movie['poster'] ?? '') ?>">
                    <?php if (!empty($movie['poster']) && strpos($movie['poster'], '/uploads/') !== false): ?>
                    <p class="text-green-400 text-sm mt-1">✓ Archivo actual: <?= basename($movie['poster']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Backdrop</label>
                    <select name="backdrop_type" id="backdrop_type" class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani mb-2" onchange="toggleBackdropFields()">
                        <option value="url" <?= !empty($movie['backdrop']) && strpos($movie['backdrop'], '/uploads/') === false ? 'selected' : '' ?>>🔗 URL</option>
                        <option value="upload" <?= !empty($movie['backdrop']) && strpos($movie['backdrop'], '/uploads/') !== false ? 'selected' : '' ?>>📁 Subir archivo</option>
                    </select>
                    <input type="url" name="backdrop_url" id="backdrop_url" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani <?= !empty($movie['backdrop']) && strpos($movie['backdrop'], '/uploads/') !== false ? 'hidden' : '' ?>"
                           value="<?= htmlspecialchars($movie['backdrop'] ?? '') ?>" placeholder="https://...">
                    <input type="file" name="backdrop_file" id="backdrop_file" accept="image/*"
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani <?= empty($movie['backdrop']) || strpos($movie['backdrop'], '/uploads/') === false ? 'hidden' : '' ?>"
                           onchange="previewBackdrop(this)">
                    <input type="hidden" name="backdrop" id="backdrop_value" value="<?= htmlspecialchars($movie['backdrop'] ?? '') ?>">
                    <?php if (!empty($movie['backdrop']) && strpos($movie['backdrop'], '/uploads/') !== false): ?>
                    <p class="text-green-400 text-sm mt-1">✓ Archivo actual: <?= basename($movie['backdrop']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">URL del Tráiler</label>
                    <input type="url" name="trailer_url" value="<?= htmlspecialchars($movie['trailer_url'] ?? '') ?>"
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">YouTube ID</label>
                    <input type="text" name="youtube_id" value="<?= htmlspecialchars($movie['youtube_id'] ?? '') ?>"
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
            </div>
            
            <hr class="border-gold/20 my-6">
            <h2 class="font-orbitron text-xl text-gold mb-4"><i class="fas fa-video mr-2"></i>Video de la Película</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Tipo de Video</label>
                    <select name="video_source" id="video_source" class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani" onchange="toggleVideoFields()">
                        <option value="upload" <?= ($movie['video_type'] ?? 'direct') === 'direct' && strpos($movie['video_url'] ?? '', '/uploads/') !== false ? 'selected' : '' ?>>📁 Subir archivo (MP4/WebM)</option>
                        <option value="youtube" <?= ($movie['video_type'] ?? '') === 'youtube' ? 'selected' : '' ?>>▶️ YouTube</option>
                        <option value="cdn" <?= ($movie['video_type'] ?? '') === 'direct' && !empty($movie['video_url']) && strpos($movie['video_url'], '/uploads/') === false && strpos($movie['video_url'], 'youtube') === false ? 'selected' : '' ?>>🔗 CDN / URL Externa</option>
                        <option value="embed" <?= ($movie['video_type'] ?? '') === 'embed' ? 'selected' : '' ?>>📱 Embed/Iframe</option>
                    </select>
                </div>
            </div>
            
            <?php 
            $videoSource = 'upload';
            if (($movie['video_type'] ?? '') === 'youtube') $videoSource = 'youtube';
            elseif (($movie['video_type'] ?? '') === 'embed') $videoSource = 'embed';
            elseif (!empty($movie['video_url']) && strpos($movie['video_url'], '/uploads/') === false) $videoSource = 'cdn';
            ?>
            
            <div id="video_upload_field" class="mt-4 <?= $videoSource !== 'upload' ? 'hidden' : '' ?>">
                <label class="block text-gold font-rajdhani mb-2">Subir Video (Max 3GB - MP4, WebM)</label>
                <input type="file" name="video_file" id="video_file" accept="video/mp4,video/webm,video/x-matroska"
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       onchange="showVideoName(this)">
                <p class="text-gray-500 text-xs mt-1">Formatos permitidos: MP4, WebM (máx 3GB)</p>
                <?php if (!empty($movie['video_url']) && strpos($movie['video_url'], '/uploads/') !== false): ?>
                <p class="text-green-400 text-sm mt-1">✓ Video actual: <?= basename($movie['video_url']) ?></p>
                <?php endif; ?>
                <input type="hidden" name="video_url" id="video_url_upload" value="<?= htmlspecialchars($movie['video_url'] ?? '') ?>">
            </div>
            
            <div id="video_youtube_field" class="mt-4 <?= $videoSource !== 'youtube' ? 'hidden' : '' ?>">
                <label class="block text-gold font-rajdhani mb-2">YouTube Video ID</label>
                <input type="text" name="youtube_video_id" id="youtube_video_id"
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       value="<?= htmlspecialchars($movie['youtube_id'] ?? '') ?>" placeholder="dQw4w9WgXcQ">
            </div>
            
            <div id="video_cdn_field" class="mt-4 <?= $videoSource !== 'cdn' ? 'hidden' : '' ?>">
                <label class="block text-gold font-rajdhani mb-2">URL del Video (CDN)</label>
                <input type="url" name="video_cdn_url" id="video_cdn_url"
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       value="<?= htmlspecialchars($movie['video_url'] ?? '') ?>" placeholder="https://cdn.example.com/video.mp4">
            </div>
            
            <div id="video_embed_field" class="mt-4 <?= $videoSource !== 'embed' ? 'hidden' : '' ?>">
                <label class="block text-gold font-rajdhani mb-2">Código Embed/Iframe</label>
                <textarea name="video_embed_code" id="video_embed_code" rows="3"
                          class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                          placeholder="<iframe src='...'></iframe>"><?= htmlspecialchars($movie['video_url'] ?? '') ?></textarea>
            </div>
            
            <input type="hidden" name="video_type" id="video_type" value="<?= $movie['video_type'] ?? 'direct' ?>">
            
            <div>
                <label class="block text-gold font-rajdhani mb-2">Géneros</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <?php foreach ($genres as $genre): ?>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="genres[]" value="<?= $genre['id'] ?>" 
                               <?= in_array($genre['id'], $currentGenreIds) ? 'checked' : '' ?> class="sr-only">
                        <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light checkbox-genre <?= in_array($genre['id'], $currentGenreIds) ? 'bg-gold' : '' ?>">
                            <i class="fas fa-check text-xs <?= in_array($genre['id'], $currentGenreIds) ? 'text-black' : 'text-gold' ?>"></i>
                        </div>
                        <span class="text-gray-300 font-rajdhani"><?= htmlspecialchars($genre['name']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Tipo de Contenido</label>
                    <select name="pricing_type" id="pricing_type" class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani" onchange="togglePrice()">
                        <option value="free" <?= $movie['is_free'] ? 'selected' : '' ?>>🎬 Gratis</option>
                        <option value="paid" <?= !$movie['is_free'] ? 'selected' : '' ?>>💰 Pago</option>
                    </select>
                </div>
                
                <div id="price_field" style="display: <?= $movie['is_free'] ? 'none' : 'block' ?>;">
                    <label class="block text-gold font-rajdhani mb-2">Precio de Renta ($)</label>
                    <input type="number" name="rental_price" value="<?= $movie['rental_price'] ?>" min="0" step="0.01" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
            </div>
            
            <input type="hidden" name="is_free" id="is_free" value="<?= $movie['is_free'] ?>">
            
            <hr class="border-gold/20 my-6">
            
            <h2 class="font-orbitron text-xl text-gold mb-4"><i class="fas fa-volume-up mr-2"></i>Pistas de Audio (Idiomas)</h2>
            
            <div id="audio-tracks-container" class="space-y-4">
                <?php if (empty($audioTracks)): ?>
                <div class="audio-track-row grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <select name="audio_tracks[0][language]" class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                            <option value="es">Español</option>
                            <option value="en">Inglés</option>
                            <option value="fr">Francés</option>
                            <option value="de">Alemán</option>
                            <option value="it">Italiano</option>
                            <option value="pt">Portugués</option>
                            <option value="ja">Japonés</option>
                            <option value="ko">Coreano</option>
                            <option value="zh">Chino</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="audio_tracks[0][label]" placeholder="Español Latino" 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div>
                        <input type="url" name="audio_tracks[0][url]" placeholder="https://..." 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="audio_tracks[0][is_default]" value="1" class="sr-only">
                            <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light" onclick="toggleCheckbox(this)">
                                <i class="fas fa-check text-gold text-xs"></i>
                            </div>
                            <span class="text-gray-300 font-rajdhani text-sm">Por defecto</span>
                        </label>
                    </div>
                </div>
                <?php else: ?>
                <?php foreach ($audioTracks as $index => $track): ?>
                <div class="audio-track-row grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <select name="audio_tracks[<?= $index ?>][language]" class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                            <option value="es" <?= $track['language'] === 'es' ? 'selected' : '' ?>>Español</option>
                            <option value="en" <?= $track['language'] === 'en' ? 'selected' : '' ?>>Inglés</option>
                            <option value="fr" <?= $track['language'] === 'fr' ? 'selected' : '' ?>>Francés</option>
                            <option value="de" <?= $track['language'] === 'de' ? 'selected' : '' ?>>Alemán</option>
                            <option value="it" <?= $track['language'] === 'it' ? 'selected' : '' ?>>Italiano</option>
                            <option value="pt" <?= $track['language'] === 'pt' ? 'selected' : '' ?>>Portugués</option>
                            <option value="ja" <?= $track['language'] === 'ja' ? 'selected' : '' ?>>Japonés</option>
                            <option value="ko" <?= $track['language'] === 'ko' ? 'selected' : '' ?>>Coreano</option>
                            <option value="zh" <?= $track['language'] === 'zh' ? 'selected' : '' ?>>Chino</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="audio_tracks[<?= $index ?>][label]" value="<?= htmlspecialchars($track['label']) ?>" 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div>
                        <input type="url" name="audio_tracks[<?= $index ?>][url]" value="<?= htmlspecialchars($track['url'] ?? '') ?>" 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="audio_tracks[<?= $index ?>][is_default]" value="1" <?= $track['is_default'] ? 'checked' : '' ?> class="sr-only">
                            <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light <?= $track['is_default'] ? 'bg-gold' : '' ?>" onclick="toggleCheckbox(this)">
                                <i class="fas fa-check text-xs <?= $track['is_default'] ? 'text-black' : 'text-gold' ?>"></i>
                            </div>
                            <span class="text-gray-300 font-rajdhani text-sm">Por defecto</span>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <button type="button" onclick="addAudioTrack()" class="text-gold hover:text-gold-light font-rajdhani text-sm">
                <i class="fas fa-plus mr-1"></i>Agregar otra pista de audio
            </button>
            
            <hr class="border-gold/20 my-6">
            
            <h2 class="font-orbitron text-xl text-gold mb-4"><i class="fas fa-closed-captioning mr-2"></i>Subtítulos</h2>
            
            <div id="subtitles-container" class="space-y-4">
                <?php if (empty($subtitles)): ?>
                <div class="subtitle-row grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <select name="subtitles[0][language]" class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                            <option value="es">Español</option>
                            <option value="en">Inglés</option>
                            <option value="fr">Francés</option>
                            <option value="de">Alemán</option>
                            <option value="it">Italiano</option>
                            <option value="pt">Portugués</option>
                            <option value="ja">Japonés</option>
                            <option value="ko">Coreano</option>
                            <option value="zh">Chino</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="subtitles[0][label]" placeholder="Español Latino" 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div>
                        <input type="url" name="subtitles[0][vtt_url]" placeholder="https://..." 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="subtitles[0][is_default]" value="1" class="sr-only">
                            <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light" onclick="toggleCheckbox(this)">
                                <i class="fas fa-check text-gold text-xs"></i>
                            </div>
                            <span class="text-gray-300 font-rajdhani text-sm">Por defecto</span>
                        </label>
                    </div>
                </div>
                <?php else: ?>
                <?php foreach ($subtitles as $index => $sub): ?>
                <div class="subtitle-row grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <select name="subtitles[<?= $index ?>][language]" class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                            <option value="es" <?= $sub['language'] === 'es' ? 'selected' : '' ?>>Español</option>
                            <option value="en" <?= $sub['language'] === 'en' ? 'selected' : '' ?>>Inglés</option>
                            <option value="fr" <?= $sub['language'] === 'fr' ? 'selected' : '' ?>>Francés</option>
                            <option value="de" <?= $sub['language'] === 'de' ? 'selected' : '' ?>>Alemán</option>
                            <option value="it" <?= $sub['language'] === 'it' ? 'selected' : '' ?>>Italiano</option>
                            <option value="pt" <?= $sub['language'] === 'pt' ? 'selected' : '' ?>>Portugués</option>
                            <option value="ja" <?= $sub['language'] === 'ja' ? 'selected' : '' ?>>Japonés</option>
                            <option value="ko" <?= $sub['language'] === 'ko' ? 'selected' : '' ?>>Coreano</option>
                            <option value="zh" <?= $sub['language'] === 'zh' ? 'selected' : '' ?>>Chino</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="subtitles[<?= $index ?>][label]" value="<?= htmlspecialchars($sub['label']) ?>" 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div>
                        <input type="url" name="subtitles[<?= $index ?>][vtt_url]" value="<?= htmlspecialchars($sub['vtt_url'] ?? '') ?>" 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="subtitles[<?= $index ?>][is_default]" value="1" <?= $sub['is_default'] ? 'checked' : '' ?> class="sr-only">
                            <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light <?= $sub['is_default'] ? 'bg-gold' : '' ?>" onclick="toggleCheckbox(this)">
                                <i class="fas fa-check text-xs <?= $sub['is_default'] ? 'text-black' : 'text-gold' ?>"></i>
                            </div>
                            <span class="text-gray-300 font-rajdhani text-sm">Por defecto</span>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <button type="button" onclick="addSubtitle()" class="text-gold hover:text-gold-light font-rajdhani text-sm">
                <i class="fas fa-plus mr-1"></i>Agregar otro subtítulo
            </button>
            
            <hr class="border-gold/20 my-6">
            
            <div class="flex justify-between">
                <a href="?route=admin/movies/delete/<?= $movieId ?>" 
                   class="px-6 py-3 rounded-lg font-rajdhani bg-red-500/20 border border-red-500 text-red-400 hover:bg-red-500/30"
                   onclick="return confirm('¿Estás seguro de eliminar esta película?');">
                    <i class="fas fa-trash mr-2"></i>Eliminar
                </a>
                
                <button type="submit" class="hud-button px-8 py-3 rounded-lg font-rajdhani font-bold">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
        
        <hr class="border-gold/20 my-6">
        
        <h2 class="font-orbitron text-xl text-gold mb-4"><i class="fas fa-ad mr-2"></i>Anuncios de la Película</h2>
        
        <form method="POST" action="?route=admin/movies/edit/<?= $movieId ?>#ads-section">
            <input type="hidden" name="update_ads" value="1">
            <div class="space-y-3">
                <?php foreach ($allAds as $ad): ?>
                <label class="flex items-center cursor-pointer p-3 rounded-lg bg-black-light/50 hover:bg-black-light">
                    <input type="checkbox" name="ads[]" value="<?= $ad['id'] ?>" 
                           <?= in_array($ad['id'], $currentAdIds) ? 'checked' : '' ?> class="sr-only">
                    <div class="w-5 h-5 border-2 border-gold rounded mr-3 flex items-center justify-center bg-black-light <?= in_array($ad['id'], $currentAdIds) ? 'bg-gold' : '' ?>" onclick="this.classList.toggle('bg-gold'); this.classList.toggle('text-black'); this.querySelector('i').classList.toggle('text-black'); this.querySelector('i').classList.toggle('text-gold');">
                        <i class="fas fa-check text-xs <?= in_array($ad['id'], $currentAdIds) ? 'text-black' : 'text-gold' ?>"></i>
                    </div>
                    <span class="text-white font-rajdhani flex-1"><?= htmlspecialchars($ad['title']) ?></span>
                    <span class="px-2 py-1 bg-gold/20 text-gold rounded text-xs"><?= $ad['ad_type'] ?></span>
                </label>
                <?php endforeach; ?>
                
                <?php if (empty($allAds)): ?>
                <p class="text-gray-400 text-center py-4">No hay anuncios disponibles. <a href="?route=admin/ads/create" class="text-gold">Crear anuncio</a></p>
                <?php endif; ?>
            </div>
            <div class="mt-4">
                <button type="submit" class="hud-button px-6 py-2 rounded-lg font-rajdhani">
                    <i class="fas fa-save mr-2"></i>Guardar Anuncios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let audioTrackCount = <?= count($audioTracks) > 0 ? count($audioTracks) : 1 ?>;
let subtitleCount = <?= count($subtitles) > 0 ? count($subtitles) : 1 ?>;

function togglePrice() {
    const pricingType = document.getElementById('pricing_type').value;
    const priceField = document.getElementById('price_field');
    const isFreeInput = document.getElementById('is_free');
    
    if (pricingType === 'paid') {
        priceField.style.display = 'block';
        isFreeInput.value = '0';
    } else {
        priceField.style.display = 'none';
        isFreeInput.value = '1';
    }
}

function togglePosterFields() {
    const type = document.getElementById('poster_type').value;
    document.getElementById('poster_url').classList.toggle('hidden', type !== 'url');
    document.getElementById('poster_file').classList.toggle('hidden', type !== 'upload');
}

function toggleBackdropFields() {
    const type = document.getElementById('backdrop_type').value;
    document.getElementById('backdrop_url').classList.toggle('hidden', type !== 'url');
    document.getElementById('backdrop_file').classList.toggle('hidden', type !== 'upload');
}

function toggleVideoFields() {
    const source = document.getElementById('video_source').value;
    const videoType = document.getElementById('video_type');
    
    document.getElementById('video_upload_field').classList.toggle('hidden', source !== 'upload');
    document.getElementById('video_youtube_field').classList.toggle('hidden', source !== 'youtube');
    document.getElementById('video_cdn_field').classList.toggle('hidden', source !== 'cdn');
    document.getElementById('video_embed_field').classList.toggle('hidden', source !== 'embed');
    
    if (source === 'upload') videoType.value = 'direct';
    else if (source === 'youtube') videoType.value = 'youtube';
    else if (source === 'cdn') videoType.value = 'direct';
    else if (source === 'embed') videoType.value = 'embed';
}

function showVideoName(input) {
    if (input.files && input.files[0]) {
        alert('Archivo seleccionado: ' + input.files[0].name + ' (' + (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB)');
    }
}

function previewPoster(input) {
    if (input.files && input.files[0]) {
        document.getElementById('poster_value').value = 'uploading...' + input.files[0].name;
    }
}

function previewBackdrop(input) {
    if (input.files && input.files[0]) {
        document.getElementById('backdrop_value').value = 'uploading...' + input.files[0].name;
    }
}

function addAudioTrack() {
    const container = document.getElementById('audio-tracks-container');
    const div = document.createElement('div');
    div.className = 'audio-track-row grid grid-cols-1 md:grid-cols-4 gap-4';
    div.innerHTML = `
        <div>
            <select name="audio_tracks[${audioTrackCount}][language]" class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                <option value="es">Español</option>
                <option value="en">Inglés</option>
                <option value="fr">Francés</option>
                <option value="de">Alemán</option>
                <option value="it">Italiano</option>
                <option value="pt">Portugués</option>
                <option value="ja">Japonés</option>
                <option value="ko">Coreano</option>
                <option value="zh">Chino</option>
            </select>
        </div>
        <div>
            <input type="text" name="audio_tracks[${audioTrackCount}][label]" placeholder="Etiqueta" 
                   class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
        </div>
        <div>
            <input type="url" name="audio_tracks[${audioTrackCount}][url]" placeholder="https://..." 
                   class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
        </div>
        <div class="flex items-center">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="audio_tracks[${audioTrackCount}][is_default]" value="1" class="sr-only">
                <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light" onclick="toggleCheckbox(this)">
                    <i class="fas fa-check text-gold text-xs"></i>
                </div>
                <span class="text-gray-300 font-rajdhani text-sm">Por defecto</span>
            </label>
        </div>
    `;
    container.appendChild(div);
    audioTrackCount++;
}

function addSubtitle() {
    const container = document.getElementById('subtitles-container');
    const div = document.createElement('div');
    div.className = 'subtitle-row grid grid-cols-1 md:grid-cols-4 gap-4';
    div.innerHTML = `
        <div>
            <select name="subtitles[${subtitleCount}][language]" class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                <option value="es">Español</option>
                <option value="en">Inglés</option>
                <option value="fr">Francés</option>
                <option value="de">Alemán</option>
                <option value="it">Italiano</option>
                <option value="pt">Portugués</option>
                <option value="ja">Japonés</option>
                <option value="ko">Coreano</option>
                <option value="zh">Chino</option>
            </select>
        </div>
        <div>
            <input type="text" name="subtitles[${subtitleCount}][label]" placeholder="Etiqueta" 
                   class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
        </div>
        <div>
            <input type="url" name="subtitles[${subtitleCount}][vtt_url]" placeholder="https://..." 
                   class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
        </div>
        <div class="flex items-center">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="subtitles[${subtitleCount}][is_default]" value="1" class="sr-only">
                <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light" onclick="toggleCheckbox(this)">
                    <i class="fas fa-check text-gold text-xs"></i>
                </div>
                <span class="text-gray-300 font-rajdhani text-sm">Por defecto</span>
            </label>
        </div>
    `;
    container.appendChild(div);
    subtitleCount++;
}

function toggleCheckbox(div) {
    const input = div.parentElement.querySelector('input');
    input.checked = !input.checked;
    if (input.checked) {
        div.classList.add('bg-gold');
        div.innerHTML = '<i class="fas fa-check text-black text-xs"></i>';
    } else {
        div.classList.remove('bg-gold');
        div.innerHTML = '<i class="fas fa-check text-gold text-xs"></i>';
    }
}

document.querySelectorAll('.checkbox-genre').forEach(checkbox => {
    checkbox.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        input.checked = !input.checked;
        if (input.checked) {
            this.classList.add('bg-gold');
            this.innerHTML = '<i class="fas fa-check text-black text-xs"></i>';
        } else {
            this.classList.remove('bg-gold');
            this.innerHTML = '<i class="fas fa-check text-gold text-xs"></i>';
        }
    });
});

document.querySelector('.checkbox-main').addEventListener('click', function() {
    const input = this.parentElement.querySelector('input');
    input.checked = !input.checked;
    if (input.checked) {
        this.classList.add('bg-gold');
        this.innerHTML = '<i class="fas fa-check text-black text-xs"></i>';
    } else {
        this.classList.remove('bg-gold');
        this.innerHTML = '<i class="fas fa-check text-gold text-xs"></i>';
    }
});
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
