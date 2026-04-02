<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Genre {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO genres (name, slug, color, description) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['slug'],
            $data['color'] ?? '#D4AF37',
            $data['description'] ?? ''
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
        
        $sql = "UPDATE genres SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->query($sql, $params);
    }
    
    public function delete($id) {
        return $this->db->query("DELETE FROM genres WHERE id = ?", [$id]);
    }
    
    public function findById($id) {
        return $this->db->fetchOne("SELECT * FROM genres WHERE id = ?", [$id]);
    }
    
    public function findBySlug($slug) {
        return $this->db->fetchOne("SELECT * FROM genres WHERE slug = ?", [$slug]);
    }
    
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM genres ORDER BY name");
    }
    
    public function getMovieCount($genreId) {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM movie_genres WHERE genre_id = ?", [$genreId]);
        return $result['count'] ?? 0;
    }
}
