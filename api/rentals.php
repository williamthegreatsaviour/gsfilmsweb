<?php
global $db, $rentalModel;

$method = $_SERVER['REQUEST_METHOD'];
$movieId = $segments[1] ?? '';

if ($method === 'GET' && $movieId === 'check' && isset($segments[2])) {
    $user = requireAuth();
    $checkMovieId = intval($segments[2]);
    
    $rental = $db->fetchOne("
        SELECT * FROM rentals 
        WHERE user_id = ? AND movie_id = ? AND is_active = 1 AND expires_at > NOW()
    ", [$user['id'], $checkMovieId]);
    
    if ($rental) {
        jsonResponse([
            'success' => true,
            'active' => true,
            'expires_at' => $rental['expires_at']
        ]);
    } else {
        jsonResponse([
            'success' => true,
            'active' => false
        ]);
    }
}

if ($method === 'GET' && empty($movieId)) {
    $user = requireAuth();
    
    $rentals = $rentalModel->getUserRentals($user['id'], false);
    
    $formatted = array_map(function($r) {
        return [
            'id' => $r['id'],
            'movie_id' => $r['movie_id'],
            'movie_title' => $r['title'],
            'movie_poster' => $r['poster'],
            'rental_price' => floatval($r['rental_price']),
            'rented_at' => $r['rented_at'],
            'expires_at' => $r['expires_at'],
            'is_active' => (bool)$r['is_active'],
            'is_expired' => strtotime($r['expires_at']) < time()
        ];
    }, $rentals);
    
    jsonResponse(['success' => true, 'data' => $formatted]);
}

jsonResponse(['error' => 'Invalid endpoint'], 404);
