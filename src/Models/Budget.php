<?php
// src/Models/Budget.php

class Budget {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getBudgetBreakdown($tripId, $userId) {
        // Verify ownership
        $stmt = $this->db->prepare("SELECT id FROM trips WHERE id = ? AND user_id = ?");
        $stmt->execute([$tripId, $userId]);
        if (!$stmt->fetch()) return false;

        // Fetch all activities with their stop city
        $stmt = $this->db->prepare("
            SELECT a.cost, a.category, s.city_name 
            FROM activities a
            JOIN trip_stops s ON a.stop_id = s.id
            WHERE s.trip_id = ? AND a.cost > 0
        ");
        $stmt->execute([$tripId]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch trip duration for avg calculation
        $stmt = $this->db->prepare("SELECT DATEDIFF(end_date, start_date) + 1 as duration FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $durationRow = $stmt->fetch();
        $duration = $durationRow['duration'] ? max(1, (int)$durationRow['duration']) : 1;

        $totalCost = 0;
        $byCategory = [
            'Transport' => 0,
            'Stay' => 0,
            'Activities' => 0,
            'Meals' => 0
        ];
        $byCity = [];

        foreach ($activities as $act) {
            $cost = (float)$act['cost'];
            $totalCost += $cost;
            
            $cat = $act['category'];
            if (!isset($byCategory[$cat])) $byCategory[$cat] = 0;
            $byCategory[$cat] += $cost;

            $city = $act['city_name'];
            if (!isset($byCity[$city])) $byCity[$city] = 0;
            $byCity[$city] += $cost;
        }

        $mostExpensiveStop = '';
        $maxStopCost = 0;
        foreach ($byCity as $city => $cost) {
            if ($cost > $maxStopCost) {
                $maxStopCost = $cost;
                $mostExpensiveStop = $city;
            }
        }

        $avgPerDay = $duration > 0 ? ($totalCost / $duration) : 0;

        return [
            'total_cost' => $totalCost,
            'avg_per_day' => $avgPerDay,
            'duration' => $duration,
            'most_expensive_stop' => [
                'city' => $mostExpensiveStop,
                'cost' => $maxStopCost
            ],
            'by_category' => $byCategory,
            'by_city' => $byCity
        ];
    }
}
