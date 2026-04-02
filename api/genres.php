<?php
global $db, $genreModel;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $genres = $genreModel->getAll();
    $formatted = array_map(function($g) {
        return [
            'id' => $g['id'],
            'name' => $g['name'],
            'slug' => $g['slug'],
            'color' => $g['color'],
            'description' => $g['description'],
            'movie_count' => $genreModel->getMovieCount($g['id'])
        ];
    }, $genres);
    
    jsonResponse(['success' => true, 'data' => $formatted]);
}

jsonResponse(['error' => 'Invalid endpoint'], 404);
