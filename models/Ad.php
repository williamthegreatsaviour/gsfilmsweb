<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Ad {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO ads (title, description, video_url, thumbnail, duration, ad_type, target_url, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['title'],
            $data['description'] ?? '',
            $data['video_url'],
            $data['thumbnail'] ?? '',
            $data['duration'] ?? 30,
            $data['ad_type'] ?? 'preroll',
            $data['target_url'] ?? '',
            $data['created_by'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }
        $params[] = $id;
        
        $sql = "UPDATE ads SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->query($sql, $params);
    }
    
    public function delete($id) {
        return $this->db->query("DELETE FROM ads WHERE id = ?", [$id]);
    }
    
    public function findById($id) {
        return $this->db->fetchOne("SELECT * FROM ads WHERE id = ?", [$id]);
    }
    
    public function getAll($activeOnly = true) {
        $sql = "SELECT * FROM ads";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getByType($type) {
        return $this->db->fetchAll("SELECT * FROM ads WHERE ad_type = ? AND is_active = 1", [$type]);
    }
    
    public function assignToMovie($movieId, $adId, $positionOrder, $startTime = 0) {
        $sql = "INSERT INTO movie_ads (movie_id, ad_id, position_order, start_time) VALUES (?, ?, ?, ?)";
        return $this->db->query($sql, [$movieId, $adId, $positionOrder, $startTime]);
    }
    
    public function removeFromMovie($movieId, $adId) {
        return $this->db->query("DELETE FROM movie_ads WHERE movie_id = ? AND ad_id = ?", [$movieId, $adId]);
    }
    
    public function getMovieAds($movieId) {
        return $this->db->fetchAll("
            SELECT a.*, ma.position_order, ma.start_time
            FROM ads a
            INNER JOIN movie_ads ma ON a.id = ma.ad_id
            WHERE ma.movie_id = ? AND a.is_active = 1
            ORDER BY ma.position_order
        ", [$movieId]);
    }
    
    public function clearMovieAds($movieId) {
        return $this->db->query("DELETE FROM movie_ads WHERE movie_id = ?", [$movieId]);
    }
}
