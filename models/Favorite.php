<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Favorite {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function add($userId, $movieId) {
        try {
            $sql = "INSERT IGNORE INTO favorites (user_id, movie_id) VALUES (?, ?)";
            $this->db->query($sql, [$userId, $movieId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function remove($userId, $movieId) {
        return $this->db->query("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?", [$userId, $movieId]);
    }
    
    public function isFavorite($userId, $movieId) {
        $result = $this->db->fetchOne("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?", [$userId, $movieId]);
        return $result !== false;
    }
    
    public function getUserFavorites($userId) {
        return $this->db->fetchAll("
            SELECT m.*, f.created_at as favorited_at
            FROM favorites f
            INNER JOIN movies m ON f.movie_id = m.id
            WHERE f.user_id = ? AND m.is_active = 1
            ORDER BY f.created_at DESC
        ", [$userId]);
    }
    
    public function getCount($movieId) {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM favorites WHERE movie_id = ?", [$movieId]);
        return $result['count'] ?? 0;
    }
}
