<?php
$pageTitle = 'Crear Película';

if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$error = '';
$success = '';
$genres = $genreModel->getAll();

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
    $subtitles = $_POST['subtitles'] ?? [];
    
    $poster = '';
    $poster_type = $_POST['poster_type'] ?? 'url';
    if ($poster_type === 'url') {
        $poster = $_POST['poster_url'] ?? '';
    } else {
        $poster = handleFileUpload('poster_file', __DIR__ . '/../../../public/uploads/posters/', ['image/jpeg', 'image/png', 'image/webp']);
    }
    
    $backdrop = '';
    $backdrop_type = $_POST['backdrop_type'] ?? 'url';
    if ($backdrop_type === 'url') {
        $backdrop = $_POST['backdrop_url'] ?? '';
    } else {
        $backdrop = handleFileUpload('backdrop_file', __DIR__ . '/../../../public/uploads/backdrops/', ['image/jpeg', 'image/png', 'image/webp']);
    }
    
    $video_source = $_POST['video_source'] ?? 'upload';
    $video_url = '';
    $video_type = 'direct';
    $youtube_id = '';
    
    if ($video_source === 'upload') {
        $video_type = 'direct';
        $video_url = handleFileUpload('video_file', __DIR__ . '/../../../public/uploads/movies/', ['video/mp4', 'video/webm', 'video/x-matroska']);
    } elseif ($video_source === 'youtube') {
        $video_type = 'youtube';
        $youtube_id = $_POST['youtube_video_id'] ?? '';
    } elseif ($video_source === 'cdn') {
        $video_type = 'direct';
        $video_url = $_POST['video_cdn_url'] ?? '';
    } elseif ($video_source === 'embed') {
        $video_type = 'embed';
        $video_url = $_POST['video_embed_code'] ?? '';
    }
    
    if (empty($title)) {
        $error = 'El título es obligatorio';
    } else {
        $movieId = $movieModel->create([
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
            'youtube_id' => $youtube_id,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);
        
        if (!empty($selected_genres)) {
            foreach ($selected_genres as $genreId) {
                $movieModel->addGenre($movieId, $genreId);
            }
        }
        
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
        
        foreach ($subtitles as $sub) {
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
        
        $success = 'Película creada correctamente';
    }
}

include __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="mb-8">
        <a href="?route=admin/movies" class="text-gold hover:text-gold-light font-rajdhani">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Películas
        </a>
    </div>
    
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-film mr-3"></i>Crear Película
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
                           placeholder="Título de la película">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Año de Estreno</label>
                    <input type="number" name="release_year" value="<?= date('Y') ?>" min="1900" max="2030" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
            </div>
            
            <div>
                <label class="block text-gold font-rajdhani mb-2">Sinopsis</label>
                <textarea name="synopsis" rows="3" 
                          class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                          placeholder="Descripción de la película"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Duración (minutos)</label>
                    <input type="number" name="duration" value="120" min="1" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Rating (0-10)</label>
                    <input type="number" name="rating" value="0" min="0" max="10" step="0.1" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani">
            </div>
            
            <!-- POSTER -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Póster</label>
                    <select name="poster_type" id="poster_type" class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani mb-2" onchange="togglePosterFields()">
                        <option value="url">🔗 URL</option>
                        <option value="upload">📁 Subir archivo</option>
                    </select>
                    <input type="url" name="poster_url" id="poster_url" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="https://...">
                    <input type="file" name="poster_file" id="poster_file" accept="image/*"
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani hidden"
                           onchange="previewPoster(this)">
                    <input type="hidden" name="poster" id="poster_value" value="">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Backdrop</label>
                    <select name="backdrop_type" id="backdrop_type" class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani mb-2" onchange="toggleBackdropFields()">
                        <option value="url">🔗 URL</option>
                        <option value="upload">📁 Subir archivo</option>
                    </select>
                    <input type="url" name="backdrop_url" id="backdrop_url" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="https://...">
                    <input type="file" name="backdrop_file" id="backdrop_file" accept="image/*"
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani hidden"
                           onchange="previewBackdrop(this)">
                    <input type="hidden" name="backdrop" id="backdrop_value" value="">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">URL del Tráiler</label>
                    <input type="url" name="trailer_url" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="https://youtube.com/...">
                </div>
                
                <div>
                    <label class="block text-gold font-rajdhani mb-2">YouTube ID (si es trailer)</label>
                    <input type="text" name="youtube_id" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                           placeholder="abc123xyz">
                </div>
            </div>
            
            <!-- VIDEO -->
            <hr class="border-gold/20 my-6">
            <h2 class="font-orbitron text-xl text-gold mb-4"><i class="fas fa-video mr-2"></i>Video de la Película</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gold font-rajdhani mb-2">Tipo de Video</label>
                    <select name="video_source" id="video_source" class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani" onchange="toggleVideoFields()">
                        <option value="upload">📁 Subir archivo (MP4/WebM)</option>
                        <option value="youtube">▶️ YouTube</option>
                        <option value="cdn">🔗 CDN / URL Externa</option>
                        <option value="embed">📱 Embed/Iframe</option>
                    </select>
                </div>
            </div>
            
            <div id="video_upload_field" class="mt-4">
                <label class="block text-gold font-rajdhani mb-2">Subir Video (Max 3GB - MP4, WebM)</label>
                <input type="file" name="video_file" id="video_file" accept="video/mp4,video/webm,video/x-matroska"
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       onchange="showVideoName(this)">
                <p class="text-gray-500 text-xs mt-1">Formatos permitidos: MP4, WebM (máx 3GB)</p>
                <input type="hidden" name="video_url" id="video_url_upload" value="">
            </div>
            
            <div id="video_youtube_field" class="mt-4 hidden">
                <label class="block text-gold font-rajdhani mb-2">YouTube Video ID</label>
                <input type="text" name="youtube_video_id" 
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       placeholder="dQw4w9WgXcQ">
            </div>
            
            <div id="video_cdn_field" class="mt-4 hidden">
                <label class="block text-gold font-rajdhani mb-2">URL del Video (CDN)</label>
                <input type="url" name="video_cdn_url" 
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       placeholder="https://cdn.example.com/video.mp4">
            </div>
            
            <div id="video_embed_field" class="mt-4 hidden">
                <label class="block text-gold font-rajdhani mb-2">Código Embed/Iframe</label>
                <textarea name="video_embed_code" rows="3"
                          class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                          placeholder="<iframe src='...'></iframe>"></textarea>
            </div>
            
            <input type="hidden" name="video_type" id="video_type" value="direct">
            </div>
            
            <div>
                <label class="block text-gold font-rajdhani mb-2">Géneros</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <?php foreach ($genres as $genre): ?>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="genres[]" value="<?= $genre['id'] ?>" class="sr-only">
                        <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light checkbox-genre">
                            <i class="fas fa-check text-gold text-xs"></i>
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
                        <option value="free">🎬 Gratis</option>
                        <option value="paid">💰 Pago</option>
                    </select>
                </div>
                
                <div id="price_field" style="display: none;">
                    <label class="block text-gold font-rajdhani mb-2">Precio de Renta ($)</label>
                    <input type="number" name="rental_price" value="0" min="0" step="0.01" 
                           class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani" placeholder="0.00">
                </div>
            </div>
            
            <input type="hidden" name="is_free" id="is_free" value="1">
            
            <hr class="border-gold/20 my-6">
            
            <h2 class="font-orbitron text-xl text-gold mb-4"><i class="fas fa-volume-up mr-2"></i>Pistas de Audio (Idiomas)</h2>
            
            <div id="audio-tracks-container" class="space-y-4">
                <div class="audio-track-row grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-400 font-rajdhani mb-1 text-sm">Idioma</label>
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
                        <label class="block text-gray-400 font-rajdhani mb-1 text-sm">Etiqueta</label>
                        <input type="text" name="audio_tracks[0][label]" placeholder="Español Latino" 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-400 font-rajdhani mb-1 text-sm">URL Audio (opcional)</label>
                        <input type="url" name="audio_tracks[0][url]" placeholder="https://..." 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer mt-6">
                            <input type="checkbox" name="audio_tracks[0][is_default]" value="1" class="sr-only">
                            <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light audio-default-check">
                                <i class="fas fa-check text-gold text-xs"></i>
                            </div>
                            <span class="text-gray-300 font-rajdhani text-sm">Por defecto</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <button type="button" onclick="addAudioTrack()" class="text-gold hover:text-gold-light font-rajdhani text-sm">
                <i class="fas fa-plus mr-1"></i>Agregar otra pista de audio
            </button>
            
            <hr class="border-gold/20 my-6">
            
            <h2 class="font-orbitron text-xl text-gold mb-4"><i class="fas fa-closed-captioning mr-2"></i>Subtítulos</h2>
            
            <div id="subtitles-container" class="space-y-4">
                <div class="subtitle-row grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-400 font-rajdhani mb-1 text-sm">Idioma</label>
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
                        <label class="block text-gray-400 font-rajdhani mb-1 text-sm">Etiqueta</label>
                        <input type="text" name="subtitles[0][label]" placeholder="Español Latino" 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-400 font-rajdhani mb-1 text-sm">URL VTT (opcional)</label>
                        <input type="url" name="subtitles[0][vtt_url]" placeholder="https://..." 
                               class="hud-input w-full px-3 py-2 rounded-lg font-rajdhani text-sm">
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer mt-6">
                            <input type="checkbox" name="subtitles[0][is_default]" value="1" class="sr-only">
                            <div class="w-5 h-5 border-2 border-gold rounded mr-2 flex items-center justify-center bg-black-light subtitle-default-check">
                                <i class="fas fa-check text-gold text-xs"></i>
                            </div>
                            <span class="text-gray-300 font-rajdhani text-sm">Por defecto</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <button type="button" onclick="addSubtitle()" class="text-gold hover:text-gold-light font-rajdhani text-sm">
                <i class="fas fa-plus mr-1"></i>Agregar otro subtítulo
            </button>
            
            <hr class="border-gold/20 my-6">
            
            <div class="flex justify-end">
                <button type="submit" class="hud-button px-8 py-3 rounded-lg font-rajdhani font-bold">
                    <i class="fas fa-save mr-2"></i>Guardar Película
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let audioTrackCount = 1;
let subtitleCount = 1;

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
    checkbox.parentElement.querySelector('input').addEventListener('change', function() {
        if (this.checked) {
            checkbox.classList.add('bg-gold');
            checkbox.innerHTML = '<i class="fas fa-check text-black text-xs"></i>';
        } else {
            checkbox.classList.remove('bg-gold');
            checkbox.innerHTML = '<i class="fas fa-check text-gold text-xs"></i>';
        }
    });
});

document.querySelector('.checkbox-main').parentElement.querySelector('input').addEventListener('change', function() {
    if (this.checked) {
        document.querySelector('.checkbox-main').classList.add('bg-gold');
        document.querySelector('.checkbox-main').innerHTML = '<i class="fas fa-check text-black text-xs"></i>';
    } else {
        document.querySelector('.checkbox-main').classList.remove('bg-gold');
        document.querySelector('.checkbox-main').innerHTML = '<i class="fas fa-check text-gold text-xs"></i>';
    }
});

document.querySelectorAll('.audio-default-check, .subtitle-default-check').forEach(checkbox => {
    checkbox.addEventListener('click', function() {
        toggleCheckbox(this);
    });
});
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
