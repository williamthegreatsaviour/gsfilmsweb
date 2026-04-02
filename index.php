<?php
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Movie.php';
require_once __DIR__ . '/models/Genre.php';
require_once __DIR__ . '/models/Rental.php';
require_once __DIR__ . '/models/Favorite.php';
require_once __DIR__ . '/models/Ad.php';
require_once __DIR__ . '/models/Settings.php';

$db = Database::getInstance();
$userModel = new User();
$movieModel = new Movie();
$genreModel = new Genre();
$rentalModel = new Rental();
$favoriteModel = new Favorite();
$adModel = new Ad();
$settingsModel = new Settings();

$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = $userModel->findById($_SESSION['user_id']);
}

$route = $_GET['route'] ?? 'home';
$routes = explode('/', $route);
$page = $routes[0];

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['super_admin', 'moderator']);
}

function isSuperAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (!empty($email) && !empty($password)) {
            $user = $userModel->verifyPassword($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: ?route=home');
                exit;
            } else {
                $loginError = 'Email o contraseña incorrectos';
            }
        }
    } elseif ($page === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (!empty($name) && !empty($email) && !empty($password)) {
            $existingUser = $userModel->findByEmail($email);
            if (!$existingUser) {
                $userId = $userModel->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'client'
                ]);
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'client';
                header('Location: ?route=home');
                exit;
            } else {
                $registerError = 'El email ya está registrado';
            }
        }
    } elseif ($page === 'logout') {
        session_destroy();
        header('Location: ?route=home');
        exit;
    }
}

// Route to appropriate view
switch ($page) {
    case 'home':
        include __DIR__ . '/views/home.php';
        break;
    case 'login':
        include __DIR__ . '/views/auth/login.php';
        break;
    case 'register':
        include __DIR__ . '/views/auth/register.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: ?route=home');
        exit;
        break;
    case 'movies':
        include __DIR__ . '/views/movies/index.php';
        break;
    case 'movie':
        include __DIR__ . '/views/movies/show.php';
        break;
    case 'player':
        include __DIR__ . '/views/movies/player.php';
        break;
    case 'search':
        include __DIR__ . '/views/movies/search.php';
        break;
    case 'genres':
    case 'genre':
        include __DIR__ . '/views/movies/index.php';
        break;
    case 'favorites':
        include __DIR__ . '/views/profile/favorites.php';
        break;
    case 'profile':
        if (isset($routes[1]) && $routes[1] === 'edit') {
            include __DIR__ . '/views/profile/edit.php';
        } else {
            include __DIR__ . '/views/profile/index.php';
        }
        break;
    case 'rentals':
        include __DIR__ . '/views/profile/rentals.php';
        break;
    case 'payment':
        if (!isLoggedIn()) {
            header('Location: ?route=login');
            exit;
        }
        if (isset($routes[1])) {
            switch ($routes[1]) {
                case 'create':
                    include __DIR__ . '/views/payment/create.php';
                    break;
                case 'success':
                    include __DIR__ . '/views/payment/success.php';
                    break;
                case 'cancel':
                    include __DIR__ . '/views/payment/cancel.php';
                    break;
                default:
                    header('Location: ?route=movies');
            }
        } else {
            header('Location: ?route=movies');
        }
        break;
    case 'admin':
        if (!isAdmin()) {
            header('Location: ?route=home');
            exit;
        }
        if (isset($routes[1])) {
            switch ($routes[1]) {
                case 'dashboard':
                    include __DIR__ . '/views/admin/dashboard.php';
                    break;
                case 'movies':
                    if (isset($routes[2])) {
                        if ($routes[2] === 'create') {
                            include __DIR__ . '/views/admin/movies/create.php';
                        } elseif ($routes[2] === 'edit' && isset($routes[3])) {
                            include __DIR__ . '/views/admin/movies/edit.php';
                        } elseif ($routes[2] === 'delete' && isset($routes[3])) {
                            $movieModel->delete($routes[3]);
                            header('Location: ?route=admin/movies');
                            exit;
                        }
                    } else {
                        include __DIR__ . '/views/admin/movies/list.php';
                    }
                    break;
                case 'ads':
                    if (isset($routes[2])) {
                        if ($routes[2] === 'create') {
                            include __DIR__ . '/views/admin/ads/create.php';
                        } elseif ($routes[2] === 'delete' && isset($routes[3])) {
                            $adModel->delete($routes[3]);
                            header('Location: ?route=admin/ads');
                            exit;
                        }
                    } else {
                        include __DIR__ . '/views/admin/ads/list.php';
                    }
                    break;
                case 'users':
                    include __DIR__ . '/views/admin/users/list.php';
                    break;
                case 'genres':
                    if (isset($routes[2])) {
                        if ($routes[2] === 'delete' && isset($routes[3])) {
                            $genreModel->delete($routes[3]);
                            header('Location: ?route=admin/genres');
                            exit;
                        }
                    } else {
                        include __DIR__ . '/views/admin/genres/list.php';
                    }
                    break;
                case 'settings':
                    include __DIR__ . '/views/admin/settings/index.php';
                    break;
                default:
                    include __DIR__ . '/views/admin/dashboard.php';
            }
        } else {
            include __DIR__ . '/views/admin/dashboard.php';
        }
        break;
    default:
        include __DIR__ . '/views/home.php';
}
