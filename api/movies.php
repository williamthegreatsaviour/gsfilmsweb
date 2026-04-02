<?php
global $db, $movieModel;

$method = $_SERVER['REQUEST_METHOD'];
$slug = $segments[1] ?? '';

function formatMovie($movie) {
    global $db;
    $genres = $db->fetchAll("
        SELECT g.* FROM genres g
        INNER JOIN movie_genres mg ON g.id = mg.genre_id
        WHERE mg.movie_id = ?
    ", [$movie['id']]);
    
    $audioTracks = $db->fetchAll("
        SELECT * FROM audio_tracks WHERE movie_id = ?
    ", [$movie['id']]);
    
    $subtitles = $db->fetchAll("
        SELECT * FROM subtitles WHERE movie_id = ?
    ", [$movie['id']]);
    
    $movieAds = $db->fetchAll("
        SELECT a.*, ma.position_order, ma.start_time 
        FROM ads a
        INNER JOIN movie_ads ma ON a.id = ma.ad_id
        WHERE ma.movie_id = ? AND a.is_active = 1
    ", [$movie['id']]);
    
    return [
        'id' => $movie['id'],
        'title' => $movie['title'],
        'slug' => $movie['slug'],
        'synopsis' => $movie['synopsis'],
        'duration' => $movie['duration'],
        'release_year' => $movie['release_year'],
        'rating' => floatval($movie['rating']),
        'poster' => $movie['poster'],
        'backdrop' => $movie['backdrop'],
        'trailer_url' => $movie['trailer_url'],
        'is_free' => (bool)$movie['is_free'],
        'rental_price' => floatval($movie['rental_price']),
        'video_url' => $movie['video_url'],
        'video_type' => $movie['video_type'],
        'youtube_id' => $movie['youtube_id'],
        'views' => $movie['views'],
        'genres' => $genres,
        'audio_tracks' => $audioTracks,
        'subtitles' => $subtitles,
        'movie_ads' => $movieAds,
        'created_at' => $movie['created_at']
    ];
}

if ($method === 'GET' && empty($slug)) {
    $page = intval($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $movies = $movieModel->getAll($limit, $offset);
    $formatted = array_map('formatMovie', $movies);
    
    jsonResponse([
        'success' => true,
        'data' => $formatted,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => count($formatted)
        ]
    ]);
}

if ($method === 'GET' && $slug === 'latest') {
    $movies = $movieModel->getLatest(10);
    $formatted = array_map('formatMovie', $movies);
    jsonResponse(['success' => true, 'data' => $formatted]);
}

if ($method === 'GET' && $slug === 'popular') {
    $movies = $movieModel->getPopular(10);
    $formatted = array_map('formatMovie', $movies);
    jsonResponse(['success' => true, 'data' => $formatted]);
}

if ($method === 'GET' && $slug === 'free') {
    $movies = $movieModel->getFree(20);
    $formatted = array_map('formatMovie', $movies);
    jsonResponse(['success' => true, 'data' => $formatted]);
}

if ($method === 'GET' && $slug === 'search') {
    $query = $_GET['q'] ?? '';
    $movies = $movieModel->search($query);
    $formatted = array_map('formatMovie', $movies);
    jsonResponse(['success' => true, 'data' => $formatted]);
}

if ($method === 'GET' && !empty($slug)) {
    $movie = $movieModel->getFullMovie($slug);
    
    if (!$movie) {
        jsonResponse(['error' => 'Movie not found'], 404);
    }
    
    $movieModel->incrementViews($movie['id']);
    $formatted = formatMovie($movie);
    
    jsonResponse(['success' => true, 'data' => $formatted]);
}

jsonResponse(['error' => 'Invalid endpoint'], 404);
