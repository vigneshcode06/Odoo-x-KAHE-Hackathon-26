<?php
class Trip {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function create($userId, $title, $description, $startDate, $endDate, $isPublic, $coverImage = null) {
        $stmt = $this->db->prepare("
            INSERT INTO trips (user_id, title, description, start_date, end_date, is_public, cover_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$userId, $title, $description, $startDate, $endDate, $isPublic, $coverImage])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getUserTrips($userId) {
        $stmt = $this->db->prepare("SELECT * FROM trips WHERE user_id = ? ORDER BY start_date ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getTripById($id, $userId = null) {
        // If userId is provided, ensure the user owns the trip (or it is public)
        if ($userId) {
            $stmt = $this->db->prepare("SELECT * FROM trips WHERE id = ? AND (user_id = ? OR is_public = 1)");
            $stmt->execute([$id, $userId]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM trips WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public function update($id, $userId, $title, $description, $startDate, $endDate, $isPublic) {
        $stmt = $this->db->prepare("
            UPDATE trips 
            SET title = ?, description = ?, start_date = ?, end_date = ?, is_public = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$title, $description, $startDate, $endDate, $isPublic, $id, $userId]);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM trips WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}
