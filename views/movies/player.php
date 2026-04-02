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

if (!$canWatch) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Acceso Denegado',
            text: 'No tienes acceso a esta película',
            background: '#1A1A1A',
            color: '#FFFFFF'
        }).then(() => {
            window.location.href = '?route=movie/{$slug}';
        });
    </script>";
    exit;
}

require_once __DIR__ . '/models/Ad.php';
$adModel = new Ad();
$movieAds = $movie['is_free'] ? $adModel->getMovieAds($movie['id']) : [];

$pageTitle = 'Reproduciendo: ' . $movie['title'];
$videoUrl = $movie['video_url'];
$videoType = $movie['video_type'];

if ($videoType === 'youtube' && !empty($movie['youtube_id'])) {
    $videoUrl = "https://www.youtube.com/embed/" . $movie['youtube_id'] . "?enablejsapi=1&rel=0";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | GSFilms</title>
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
    <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: { DEFAULT: '#D4AF37', light: '#FFD700', dark: '#B8860B' },
                        black: { DEFAULT: '#0A0A0A', light: '#1A1A1A' }
                    },
                    fontFamily: {
                        orbitron: ['Orbitron', 'sans-serif'],
                        rajdhani: ['Rajdhani', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { background: #0A0A0A; margin: 0; overflow: hidden; }
        .video-js { width: 100%; height: 100vh; }
        .vjs-control-bar { background: linear-gradient(to top, rgba(0,0,0,0.9), transparent) !important; }
        .vjs-big-play-button { background: rgba(212, 175, 55, 0.8) !important; border: none !important; border-radius: 50% !important; width: 80px !important; height: 80px !important; line-height: 80px !important; }
        .vjs-menu-button { color: #D4AF37 !important; }
        .vjs-selected { color: #FFD700 !important; }
        .ad-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.95); z-index: 9999; display: flex; align-items: center; justify-content: center; }
        .ad-video { max-width: 90%; max-height: 80%; }
        .ad-countdown { position: absolute; top: 20px; right: 20px; background: rgba(212, 175, 55, 0.9); color: black; padding: 10px 20px; border-radius: 5px; font-family: 'Rajdhani', sans-serif; font-weight: bold; }
    </style>
</head>
<body>
    <div class="fixed top-4 left-4 z-50">
        <a href="?route=movie/<?= $slug ?>" class="bg-black/80 hover:bg-gold/20 text-white hover:text-gold px-4 py-2 rounded-lg font-rajdhani transition flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <?php if ($videoType === 'youtube'): ?>
    <iframe id="youtube-player" 
            src="<?= $videoUrl ?>"
            class="w-full h-screen"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
    </iframe>
    <?php else: ?>
    <video id="movie-player" 
           class="video-js vjs-theme-forest"
           controls 
           preload="auto"
           playsinline>
        <source src="<?= $videoUrl ?>" type="video/mp4">
        
        <?php foreach ($movie['audio_tracks'] as $index => $track): ?>
        <audio ref="<?= $index ?>" kind="audio" label="<?= htmlspecialchars($track['label']) ?>" srclang="<?= $track['language'] ?>" <?= $track['is_default'] ? 'default' : '' ?>>
            <?php if (!empty($track['url'])): ?>
            <source src="<?= $track['url'] ?>" type="audio/mp4">
            <?php endif; ?>
        </audio>
        <?php endforeach; ?>
        
        <?php foreach ($movie['subtitles'] as $track): ?>
        <track kind="subtitles" 
               label="<?= htmlspecialchars($track['label']) ?>" 
               srclang="<?= $track['language'] ?>" 
               src="<?= $track['vtt_url'] ?>" 
               <?= $track['is_default'] ? 'default' : '' ?>>
        <?php endforeach; ?>
        
        <p class="vjs-no-js">
            To view this video please enable JavaScript.
        </p>
    </video>
    
    <script>
        const player = videojs('movie-player', {
            html5: {
                vhs: {
                    overrideNative: true
                }
            },
            playbackRates: [0.5, 1, 1.25, 1.5, 2],
            controlBar: {
                children: [
                    'playToggle',
                    'volumePanel',
                    'currentTimeDisplay',
                    'timeDivider',
                    'durationDisplay',
                    'progressControl',
                    'playbackRateMenuButton',
                    'fullscreenToggle',
                    'qualitySelector'
                ]
            }
        });
        
        player.on('loadedmetadata', function() {
            console.log('Player loaded with tracks:', player.textTracks());
        });
        
        player.on('play', function() {
            console.log('Video playing');
        });
        
        player.on('error', function() {
            console.error('Player error:', player.error());
        });
    </script>
    <?php endif; ?>

    <?php if (!empty($movieAds)): ?>
    <script>
        const ads = <?= json_encode($movieAds) ?>;
        let currentAdIndex = 0;
        let adPlaying = false;
        
        function showAd(ad, callback) {
            adPlaying = true;
            const overlay = document.createElement('div');
            overlay.id = 'ad-overlay';
            overlay.className = 'ad-overlay';
            overlay.innerHTML = `
                <div class="ad-countdown">Saltando en ${ad.duration} segundos...</div>
                <video id="ad-video" class="ad-video" autoplay>
                    <source src="${ad.video_url}" type="video/mp4">
                </video>
            `;
            document.body.appendChild(overlay);
            
            const adVideo = document.getElementById('ad-video');
            let countdown = ad.duration;
            
            const timer = setInterval(() => {
                countdown--;
                overlay.querySelector('.ad-countdown').textContent = `Saltando en ${countdown} segundos...`;
                if (countdown <= 0) clearInterval(timer);
            }, 1000);
            
            adVideo.addEventListener('ended', () => {
                document.body.removeChild(overlay);
                adPlaying = false;
                callback();
            });
        }
        
        function playPreroll(callback) {
            const prerolls = ads.filter(a => a.ad_type === 'preroll');
            if (prerolls.length > 0) {
                showAd(prerolls[0], callback);
            } else {
                callback();
            }
        }
        
        function setupMidrolls() {
            const midrolls = ads.filter(a => a.ad_type === 'midroll');
            if (midrolls.length === 0) return;
            
            const player = document.getElementById('movie-player');
            if (!player) return;
            
            midrolls.forEach(ad => {
                player.addEventListener('timeupdate', function() {
                    if (Math.abs(player.currentTime - ad.start_time) < 1 && !ad.hasPlayed) {
                        ad.hasPlayed = true;
                        player.pause();
                        showAd(ad, () => player.play());
                    }
                });
            });
        }
        
        function playPostroll() {
            const postrolls = ads.filter(a => a.ad_type === 'postroll');
            if (postrolls.length > 0 && !adPlaying) {
                showAd(postrolls[0], () => {});
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const playerEl = document.getElementById('movie-player');
            if (playerEl && playerEl.player) {
                playPreroll(() => {
                    setupMidrolls();
                    
                    playerEl.player.addEventListener('ended', function() {
                        playPostroll();
                    });
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
