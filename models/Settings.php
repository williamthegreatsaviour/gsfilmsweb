<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Settings {
    private $db;
    private static $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function get($key, $default = null) {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $result = $this->db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        $value = $result ? $result['setting_value'] : $default;
        
        self::$cache[$key] = $value;
        return $value;
    }
    
    public function set($key, $value) {
        $this->db->query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE setting_value = ?",
            [$key, $value, $value]
        );
        
        self::$cache[$key] = $value;
        return true;
    }
    
    public function getAll() {
        $results = $this->db->fetchAll("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    public function delete($key) {
        return $this->db->query("DELETE FROM settings WHERE setting_key = ?", [$key]);
    }
    
    public static function clearCache() {
        self::$cache = [];
    }
}
