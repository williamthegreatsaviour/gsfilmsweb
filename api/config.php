<?php
global $settingsModel;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $settings = $settingsModel->getAll();
    
    jsonResponse([
        'success' => true,
        'data' => [
            'site_name' => $settings['site_name'] ?? 'GSFilms',
            'site_email' => $settings['site_email'] ?? '',
            'site_phone' => $settings['site_phone'] ?? '',
            'site_facebook' => $settings['site_facebook'] ?? '',
            'site_twitter' => $settings['site_twitter'] ?? '',
            'site_instagram' => $settings['site_instagram'] ?? '',
            'site_youtube' => $settings['site_youtube'] ?? '',
            'rental_duration_hours' => intval($settings['rental_duration_hours'] ?? 48),
            'stripe_configured' => !empty($settings['stripe_secret_key']) && strpos($settings['stripe_secret_key'], 'sk_') === 0
        ]
    ]);
}

jsonResponse(['error' => 'Invalid endpoint'], 404);
