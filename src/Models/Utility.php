<?php
class Utility {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Packing Items
    public function getPackingItems($tripId) {
        $stmt = $this->db->prepare("SELECT * FROM packing_items WHERE trip_id = ?");
        $stmt->execute([$tripId]);
        return $stmt->fetchAll();
    }

    public function addPackingItem($tripId, $itemName, $category = 'Misc') {
        $stmt = $this->db->prepare("INSERT INTO packing_items (trip_id, item_name, category) VALUES (?, ?, ?)");
        return $stmt->execute([$tripId, $itemName, $category]);
    }

    public function togglePackingItem($id, $isPacked) {
        $stmt = $this->db->prepare("UPDATE packing_items SET is_packed = ? WHERE id = ?");
        return $stmt->execute([$isPacked, $id]);
    }

    public function deletePackingItem($id) {
        $stmt = $this->db->prepare("DELETE FROM packing_items WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function resetPackingItems($tripId) {
        $stmt = $this->db->prepare("UPDATE packing_items SET is_packed = 0 WHERE trip_id = ?");
        return $stmt->execute([$tripId]);
    }

    // Trip Notes
    public function getNotes($tripId) {
        $stmt = $this->db->prepare("SELECT * FROM trip_notes WHERE trip_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tripId]);
        return $stmt->fetchAll();
    }

    public function addNote($tripId, $content) {
        $stmt = $this->db->prepare("INSERT INTO trip_notes (trip_id, content) VALUES (?, ?)");
        return $stmt->execute([$tripId, $content]);
    }

    public function deleteNote($id) {
        $stmt = $this->db->prepare("DELETE FROM trip_notes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateNote($id, $content) {
        $stmt = $this->db->prepare("UPDATE trip_notes SET content = ? WHERE id = ?");
        return $stmt->execute([$content, $id]);
    }
}
