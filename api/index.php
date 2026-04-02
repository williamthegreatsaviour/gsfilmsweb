<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Genre.php';
require_once __DIR__ . '/../models/Rental.php';
require_once __DIR__ . '/../models/Favorite.php';
require_once __DIR__ . '/../models/Settings.php';

$db = Database::getInstance();
$userModel = new User();
$movieModel = new Movie();
$genreModel = new Genre();
$rentalModel = new Rental();
$favoriteModel = new Favorite();
$settingsModel = new Settings();

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getBearerToken() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
        return $matches[1];
    }
    return null;
}

function requireAuth() {
    global $userModel;
    $token = getBearerToken();
    if (!$token) {
        jsonResponse(['error' => 'Unauthorized - No token'], 401);
    }
    $user = $userModel->verifyToken($token);
    if (!$user) {
        jsonResponse(['error' => 'Unauthorized - Invalid token'], 401);
    }
    return $user;
}

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove base path - handle different installations
$path = str_replace('/api', '', $path);
$path = trim($path, '/');

// If path is empty or just /, it's the root
if (empty($path)) {
    $path = '';
}

$segments = array_filter(explode('/', $path));
$segments = array_values($segments);

$route = $segments[0] ?? '';

switch ($route) {
    case 'auth':
        require_once __DIR__ . '/auth.php';
        break;
    case 'movies':
        require_once __DIR__ . '/movies.php';
        break;
    case 'genres':
        require_once __DIR__ . '/genres.php';
        break;
    case 'rentals':
        require_once __DIR__ . '/rentals.php';
        break;
    case 'favorites':
        require_once __DIR__ . '/favorites.php';
        break;
    case 'config':
        require_once __DIR__ . '/config.php';
        break;
    default:
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . '://' . $host;
        
        jsonResponse([
            'name' => 'GSFilms API',
            'version' => '1.0.0',
            'base_url' => $baseUrl,
            'endpoints' => [
                'GET /api' => 'API Info',
                'POST /api/auth/login' => 'User login',
                'POST /api/auth/register' => 'User registration',
                'GET /api/auth/me' => 'Get current user',
                'GET /api/movies' => 'List all movies',
                'GET /api/movies/{slug}' => 'Get movie by slug',
                'GET /api/movies/latest' => 'Get latest movies',
                'GET /api/movies/popular' => 'Get popular movies',
                'GET /api/movies/free' => 'Get free movies',
                'GET /api/genres' => 'List genres',
                'GET /api/rentals' => 'User rentals',
                'GET /api/favorites' => 'User favorites',
                'GET /api/config' => 'Site configuration'
            ]
        ]);
}
