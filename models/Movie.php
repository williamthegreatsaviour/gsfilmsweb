<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Movie {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO movies (title, slug, synopsis, duration, release_year, rating, poster, backdrop, trailer_url, is_free, rental_price, video_url, video_type, youtube_id, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['title'],
            $data['slug'],
            $data['synopsis'] ?? '',
            $data['duration'] ?? 0,
            $data['release_year'] ?? date('Y'),
            $data['rating'] ?? 0,
            $data['poster'] ?? '',
            $data['backdrop'] ?? '',
            $data['trailer_url'] ?? '',
            $data['is_free'] ?? true,
            $data['rental_price'] ?? 0,
            $data['video_url'] ?? '',
            $data['video_type'] ?? 'direct',
            $data['youtube_id'] ?? '',
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
        
        $sql = "UPDATE movies SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, $params);
    }
    
    public function delete($id) {
        return $this->db->query("DELETE FROM movies WHERE id = ?", [$id]);
    }
    
    public function findById($id) {
        return $this->db->fetchOne("SELECT * FROM movies WHERE id = ?", [$id]);
    }
    
    public function findBySlug($slug) {
        return $this->db->fetchOne("SELECT * FROM movies WHERE slug = ? AND is_active = 1", [$slug]);
    }
    
    public function getAll($limit = 50, $offset = 0) {
        return $this->db->fetchAll("SELECT * FROM movies WHERE is_active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);
    }
    
    public function getFree($limit = 50) {
        return $this->db->fetchAll("SELECT * FROM movies WHERE is_free = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT ?", [$limit]);
    }
    
    public function getPaid($limit = 50) {
        return $this->db->fetchAll("SELECT * FROM movies WHERE is_free = 0 AND is_active = 1 ORDER BY created_at DESC LIMIT ?", [$limit]);
    }
    
    public function getLatest($limit = 10) {
        return $this->db->fetchAll("SELECT * FROM movies WHERE is_active = 1 ORDER BY release_year DESC, created_at DESC LIMIT ?", [$limit]);
    }
    
    public function getPopular($limit = 10) {
        return $this->db->fetchAll("SELECT * FROM movies WHERE is_active = 1 ORDER BY views DESC LIMIT ?", [$limit]);
    }
    
    public function search($query) {
        $searchTerm = "%{$query}%";
        return $this->db->fetchAll("SELECT * FROM movies WHERE is_active = 1 AND (title LIKE ? OR synopsis LIKE ?) ORDER BY views DESC LIMIT 50", [$searchTerm, $searchTerm]);
    }
    
    public function getByGenre($genreId, $limit = 50) {
        return $this->db->fetchAll("
            SELECT m.* FROM movies m
            INNER JOIN movie_genres mg ON m.id = mg.movie_id
            WHERE mg.genre_id = ? AND m.is_active = 1
            ORDER BY m.created_at DESC LIMIT ?
        ", [$genreId, $limit]);
    }
    
    public function addGenre($movieId, $genreId) {
        return $this->db->query("INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (?, ?)", [$movieId, $genreId]);
    }
    
    public function setGenres($movieId, $genreIds) {
        $this->db->query("DELETE FROM movie_genres WHERE movie_id = ?", [$movieId]);
        foreach ($genreIds as $genreId) {
            $this->addGenre($movieId, $genreId);
        }
    }
    
    public function getGenres($movieId) {
        return $this->db->fetchAll("
            SELECT g.* FROM genres g
            INNER JOIN movie_genres mg ON g.id = mg.genre_id
            WHERE mg.movie_id = ?
        ", [$movieId]);
    }
    
    public function addCastMember($movieId, $data) {
        $sql = "INSERT INTO cast_members (movie_id, name, role, image) VALUES (?, ?, ?, ?)";
        return $this->db->query($sql, [$movieId, $data['name'], $data['role'] ?? 'Actor', $data['image'] ?? '']);
    }
    
    public function getCast($movieId) {
        return $this->db->fetchAll("SELECT * FROM cast_members WHERE movie_id = ? ORDER BY id", [$movieId]);
    }
    
    public function addAudioTrack($movieId, $data) {
        $sql = "INSERT INTO audio_tracks (movie_id, language, label, url, is_default) VALUES (?, ?, ?, ?, ?)";
        return $this->db->query($sql, [
            $movieId,
            $data['language'],
            $data['label'],
            $data['url'] ?? '',
            $data['is_default'] ?? false
        ]);
    }
    
    public function getAudioTracks($movieId) {
        return $this->db->fetchAll("SELECT * FROM audio_tracks WHERE movie_id = ?", [$movieId]);
    }
    
    public function addSubtitle($movieId, $data) {
        $sql = "INSERT INTO subtitles (movie_id, language, label, vtt_url, vtt_content, is_default) VALUES (?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, [
            $movieId,
            $data['language'],
            $data['label'],
            $data['vtt_url'] ?? '',
            $data['vtt_content'] ?? '',
            $data['is_default'] ?? false
        ]);
    }
    
    public function getSubtitles($movieId) {
        return $this->db->fetchAll("SELECT * FROM subtitles WHERE movie_id = ?", [$movieId]);
    }
    
    public function incrementViews($id) {
        return $this->db->query("UPDATE movies SET views = views + 1 WHERE id = ?", [$id]);
    }
    
    public function getFullMovie($slug) {
        $movie = $this->findBySlug($slug);
        if ($movie) {
            $movie['genres'] = $this->getGenres($movie['id']);
            $movie['cast'] = $this->getCast($movie['id']);
            $movie['audio_tracks'] = $this->getAudioTracks($movie['id']);
            $movie['subtitles'] = $this->getSubtitles($movie['id']);
        }
        return $movie;
    }
}
