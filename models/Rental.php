<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/config/Config.php';

class Rental {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($userId, $movieId, $price, $stripePaymentIntent = null) {
        $rentalHours = 48; // Duración por defecto de alquiler
        $expiresAt = date('Y-m-d H:i:s', time() + ($rentalHours * 3600));
        
        $sql = "INSERT INTO rentals (user_id, movie_id, rental_price, expires_at, stripe_payment_intent, is_active) 
                VALUES (?, ?, ?, ?, ?, 1)";
        
        $this->db->query($sql, [$userId, $movieId, $price, $expiresAt, $stripePaymentIntent]);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        return $this->db->fetchOne("SELECT * FROM rentals WHERE id = ?", [$id]);
    }
    
    public function getUserRentals($userId, $activeOnly = true) {
        $sql = "SELECT r.*, m.title, m.poster, m.slug, m.duration 
                FROM rentals r 
                INNER JOIN movies m ON r.movie_id = m.id 
                WHERE r.user_id = ?";
        
        if ($activeOnly) {
            $sql .= " AND r.is_active = 1 AND r.expires_at > NOW()";
        }
        
        $sql .= " ORDER BY r.rented_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    public function hasActiveRental($userId, $movieId) {
        $sql = "SELECT * FROM rentals 
                WHERE user_id = ? AND movie_id = ? AND is_active = 1 AND expires_at > NOW() 
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$userId, $movieId]);
    }
    
    public function isRentalActive($rentalId) {
        $rental = $this->findById($rentalId);
        if (!$rental) return false;
        
        return $rental['is_active'] && strtotime($rental['expires_at']) > time();
    }
    
    public function deactivate($id) {
        return $this->db->query("UPDATE rentals SET is_active = 0 WHERE id = ?", [$id]);
    }
    
    public function checkAndExpire() {
        return $this->db->query("UPDATE rentals SET is_active = 0 WHERE is_active = 1 AND expires_at <= NOW()");
    }
    
    public function getRentalWithMovie($userId, $movieId) {
        $sql = "SELECT r.*, m.title, m.poster, m.slug, m.video_url, m.video_type, m.youtube_id 
                FROM rentals r 
                INNER JOIN movies m ON r.movie_id = m.id 
                WHERE r.user_id = ? AND r.movie_id = ? AND r.is_active = 1 AND r.expires_at > NOW() 
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$userId, $movieId]);
    }
}
