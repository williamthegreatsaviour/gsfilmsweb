<?php
require_once __DIR__ . '/Database.php';

class Config {
    const DEFAULT_APP_NAME = 'GSFilms';
    
    private static $settings = null;
    
    private static function getDynamicBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
    
    private static function loadSettings() {
        if (self::$settings === null) {
            try {
                $db = Database::getInstance();
                $results = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
                self::$settings = [];
                foreach ($results as $row) {
                    self::$settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Exception $e) {
                self::$settings = [];
            }
        }
        return self::$settings;
    }
    
    private static function get($key, $default = null) {
        $settings = self::loadSettings();
        return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $default;
    }
    
    public static function getBaseUrl() {
        $savedUrl = self::get('site_url');
        return !empty($savedUrl) ? $savedUrl : self::getDynamicBaseUrl();
    }
    
    public static function getAppName() {
        return self::get('site_name', self::DEFAULT_APP_NAME);
    }
    
    public static function getSiteEmail() {
        return self::get('site_email', 'contact@gsfilms.com');
    }
    
    public static function getSitePhone() {
        return self::get('site_phone', '');
    }
    
    public static function getSiteFacebook() {
        return self::get('site_facebook', '');
    }
    
    public static function getSiteTwitter() {
        return self::get('site_twitter', '');
    }
    
    public static function getSiteInstagram() {
        return self::get('site_instagram', '');
    }
    
    public static function getSiteYoutube() {
        return self::get('site_youtube', '');
    }
    
    public static function getStripeSecretKey() {
        return self::get('stripe_secret_key', '');
    }
    
    public static function getStripePublishableKey() {
        return self::get('stripe_publishable_key', '');
    }
    
    public static function getStripeWebhookSecret() {
        return self::get('stripe_webhook_secret', '');
    }
    
    public static function isStripeConfigured() {
        $secret = self::getStripeSecretKey();
        return !empty($secret) && strpos($secret, 'sk_') === 0;
    }
    
    public static function getRentalDurationHours() {
        return intval(self::get('rental_duration_hours', 48));
    }
    
    public static function getUploadPath() {
        return dirname(__DIR__) . '/../public/uploads/';
    }
    
    public static function getMaxUploadSize() {
        return 5000000000;
    }
    
    public static function getJwtSecret() {
        return self::get('jwt_secret', 'your_jwt_secret_key_here');
    }
    
    public static function getJwtExpiry() {
        return 86400;
    }
    
    public static function getUploadUrl($filename) {
        return self::getBaseUrl() . '/uploads/' . $filename;
    }
}
