<?php
class Itinerary {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getStopsByTrip($tripId) {
        $stmt = $this->db->prepare("SELECT * FROM trip_stops WHERE trip_id = ? ORDER BY order_index ASC");
        $stmt->execute([$tripId]);
        return $stmt->fetchAll();
    }

    public function getActivitiesByStop($stopId) {
        $stmt = $this->db->prepare("SELECT * FROM activities WHERE stop_id = ? ORDER BY start_time ASC");
        $stmt->execute([$stopId]);
        return $stmt->fetchAll();
    }

    public function addStop($tripId, $cityName, $arrivalDate, $departureDate, $orderIndex) {
        $stmt = $this->db->prepare("
            INSERT INTO trip_stops (trip_id, city_name, arrival_date, departure_date, order_index)
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$tripId, $cityName, $arrivalDate, $departureDate, $orderIndex])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function addActivity($stopId, $title, $description, $startTime, $cost) {
        $stmt = $this->db->prepare("
            INSERT INTO activities (stop_id, title, description, start_time, cost)
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$stopId, $title, $description, $startTime, $cost])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateStop($id, $cityName, $arrivalDate, $departureDate) {
        $stmt = $this->db->prepare("
            UPDATE trip_stops SET city_name = ?, arrival_date = ?, departure_date = ? WHERE id = ?
        ");
        return $stmt->execute([$cityName, $arrivalDate, $departureDate, $id]);
    }

    public function updateActivity($id, $title, $startTime, $cost) {
        $stmt = $this->db->prepare("
            UPDATE activities SET title = ?, start_time = ?, cost = ? WHERE id = ?
        ");
        return $stmt->execute([$title, $startTime, $cost, $id]);
    }
    
    // Check if user owns the trip (for authorization)
    public function verifyTripOwnership($tripId, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM trips WHERE id = ? AND user_id = ?");
        $stmt->execute([$tripId, $userId]);
        return $stmt->fetch() !== false;
    }

    public function deleteStop($id) {
        $stmt = $this->db->prepare("DELETE FROM trip_stops WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteActivity($id) {
        $stmt = $this->db->prepare("DELETE FROM activities WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function reorderStops($stopsOrder) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE trip_stops SET order_index = ? WHERE id = ?");
            foreach ($stopsOrder as $index => $stopId) {
                $stmt->execute([$index, $stopId]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    

    
    public function getFullItinerary($tripId) {
        $stops = $this->getStopsByTrip($tripId);
        foreach ($stops as &$stop) {
            $stop['activities'] = $this->getActivitiesByStop($stop['id']);
        }
        return $stops;
    }
}
