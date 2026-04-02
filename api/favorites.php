<?php
global $db, $favoriteModel, $movieModel;

$method = $_SERVER['REQUEST_METHOD'];
$movieId = $segments[1] ?? '';

function formatFavorite($fav, $movie) {
    if (!$movie) return null;
    return [
        'id' => $fav['id'],
        'movie_id' => $fav['movie_id'],
        'movie' => [
            'id' => $movie['id'],
            'title' => $movie['title'],
            'slug' => $movie['slug'],
            'poster' => $movie['poster'],
            'backdrop' => $movie['backdrop'],
            'is_free' => (bool)$movie['is_free'],
            'rental_price' => floatval($movie['rental_price']),
            'release_year' => $movie['release_year'],
            'duration' => $movie['duration']
        ],
        'created_at' => $fav['created_at']
    ];
}

if ($method === 'GET') {
    $user = requireAuth();
    $favorites = $favoriteModel->getUserFavorites($user['id']);
    
    $formatted = [];
    foreach ($favorites as $fav) {
        $movie = $movieModel->findById($fav['movie_id']);
        if ($movie) {
            $formatted[] = formatFavorite($fav, $movie);
        }
    }
    
    jsonResponse(['success' => true, 'data' => $formatted]);
}

if ($method === 'POST') {
    $user = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $movieId = intval($data['movie_id'] ?? 0);
    
    if (!$movieId) {
        jsonResponse(['error' => 'Movie ID required'], 400);
    }
    
    $existing = $db->fetchOne(
        "SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?",
        [$user['id'], $movieId]
    );
    
    if ($existing) {
        jsonResponse(['success' => true, 'message' => 'Already in favorites']);
    }
    
    $favoriteModel->add($user['id'], $movieId);
    jsonResponse(['success' => true, 'message' => 'Added to favorites'], 201);
}

if ($method === 'DELETE' && !empty($movieId)) {
    $user = requireAuth();
    $movieId = intval($movieId);
    
    $favoriteModel->remove($user['id'], $movieId);
    jsonResponse(['success' => true, 'message' => 'Removed from favorites']);
}

jsonResponse(['error' => 'Invalid endpoint'], 404);
