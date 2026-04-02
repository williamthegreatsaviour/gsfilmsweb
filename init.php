<?php
// init.php - Archivo de inicialización común para todas las páginas
session_start();

// Incluir configuración de base de datos
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';

// Inicializar base de datos y modelo de usuario
$db = Database::getInstance();
$userModel = new User();

// Variable para usuario actual
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = $userModel->findById($_SESSION['user_id']);
}

// Funciones de utilidad
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['super_admin', 'moderator']);
}

function isSuperAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin';
}

function isClient() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'client';
}

function isGuest() {
    return !isset($_SESSION['user_id']);
}
