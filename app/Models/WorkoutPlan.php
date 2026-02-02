<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use DateTime;

class WorkoutPlan
{
    public static function all()
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT wp.*, 
             CONCAT(m.first_name, ' ', m.last_name) as member_name,
             CONCAT(t.first_name, ' ', t.last_name) as trainer_name
             FROM workout_plans wp
             LEFT JOIN members m ON wp.member_id = m.id
             LEFT JOIN trainers t ON wp.trainer_id = t.id
             ORDER BY wp.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT wp.*, 
             CONCAT(m.first_name, ' ', m.last_name) as member_name,
             CONCAT(t.first_name, ' ', t.last_name) as trainer_name,
             m.email as member_email,
             t.email as trainer_email
             FROM workout_plans wp
             LEFT JOIN members m ON wp.member_id = m.id
             LEFT JOIN trainers t ON wp.trainer_id = t.id
             WHERE wp.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByMember($memberId)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT wp.*, 
             CONCAT(t.first_name, ' ', t.last_name) as trainer_name
             FROM workout_plans wp
             LEFT JOIN trainers t ON wp.trainer_id = t.id
             WHERE wp.member_id = ?
             ORDER BY wp.created_at DESC"
        );
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    public static function getByTrainer($trainerId)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT wp.*, 
             CONCAT(m.first_name, ' ', m.last_name) as member_name
             FROM workout_plans wp
             LEFT JOIN members m ON wp.member_id = m.id
             WHERE wp.trainer_id = ?
             ORDER BY wp.created_at DESC"
        );
        $stmt->execute([$trainerId]);
        return $stmt->fetchAll();
    }

    public static function create($data)
    {
        $db = Database::connect();

        // Calculate duration in weeks if not provided
        $durationWeeks = null;
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start = new DateTime($data['start_date']);
            $end = new DateTime($data['end_date']);
            $diff = $start->diff($end);
            $durationWeeks = round($diff->days / 7);
        }

        $stmt = $db->prepare(
            "INSERT INTO workout_plans (member_id, trainer_id, plan_name, plan_type, description, 
             duration_weeks, start_date, end_date, sessions_per_week, session_duration, 
             difficulty_level, goals, notes, exercises, status, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        // Handle exercises as JSON
        $exercises = [];
        if (isset($data['exercises'])) {
            foreach ($data['exercises'] as $exercise) {
                if (!empty($exercise['name'])) {
                    $exercises[] = $exercise;
                }
            }
        }

        $result = $stmt->execute([
            $data['member_id'],
            $data['trainer_id'] ?? null,
            $data['plan_name'],
            $data['plan_type'] ?? null,
            $data['plan_description'] ?? null,
            $durationWeeks,
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['sessions_per_week'] ?? null,
            $data['session_duration'] ?? null,
            $data['difficulty_level'] ?? null,
            $data['goals'] ?? null,
            $data['notes'] ?? null,
            json_encode($exercises),
            isset($data['is_active']) ? 'Active' : 'Draft'
        ]);

        return $result ? $db->lastInsertId() : false;
    }

    public static function update($id, $data)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "UPDATE workout_plans SET member_id = ?, trainer_id = ?, plan_name = ?, 
             description = ?, duration_weeks = ?, start_date = ?, end_date = ?, 
             status = ? WHERE id = ?"
        );

        return $stmt->execute([
            $data['member_id'],
            $data['trainer_id'] ?? null,
            $data['plan_name'],
            $data['description'],
            $data['duration_weeks'],
            $data['start_date'],
            $data['end_date'],
            $data['status'],
            $id
        ]);
    }

    public static function delete($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM workout_plans WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getStats()
    {
        // First, update any expired plans to completed status
        self::updateExpiredPlans();
        
        $db = Database::connect();
        $stats = [];

        // Total plans
        $stats['total_plans'] = $db->query("SELECT COUNT(*) as count FROM workout_plans")->fetch()['count'];

        // Active plans
        $stats['active_plans'] = $db->query("SELECT COUNT(*) as count FROM workout_plans WHERE status = 'Active'")->fetch()['count'];

        // Completed plans
        $stats['completed_plans'] = $db->query("SELECT COUNT(*) as count FROM workout_plans WHERE status = 'Completed'")->fetch()['count'];

        // Plans this month
        $stats['plans_this_month'] = $db->query("SELECT COUNT(*) as count FROM workout_plans WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetch()['count'];

        return $stats;
    }

    public static function search($params)
    {
        $db = Database::connect();
        $sql = "SELECT wp.*, 
                CONCAT(m.first_name, ' ', m.last_name) as member_name,
                m.email as member_email,
                CONCAT(t.first_name, ' ', t.last_name) as trainer_name,
                t.specialization as trainer_specialization
                FROM workout_plans wp
                LEFT JOIN members m ON wp.member_id = m.id
                LEFT JOIN trainers t ON wp.trainer_id = t.id
                WHERE 1=1";
        $bindings = [];

        if (!empty($params['search'])) {
            $searchTerm = '%' . $params['search'] . '%';
            $sql .= " AND (
                wp.plan_name LIKE ? OR 
                CONCAT(m.first_name, ' ', m.last_name) LIKE ? OR 
                CONCAT(t.first_name, ' ', t.last_name) LIKE ?
            )";
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
        }

        if (!empty($params['trainer_id'])) {
            $sql .= " AND wp.trainer_id = ?";
            $bindings[] = $params['trainer_id'];
        }

        if (!empty($params['status'])) {
            $status = strtolower($params['status']);

            if ($status === 'active') {
                $sql .= " AND wp.status = 'Active'";
            } elseif ($status === 'completed') {
                $sql .= " AND wp.status = 'Completed'";
            } elseif ($status === 'inactive') {
                // Not Active and Not Completed (e.g. Draft, Cancelled)
                $sql .= " AND wp.status NOT IN ('Active', 'Completed')";
            } else {
                $sql .= " AND wp.status = ?";
                $bindings[] = ucfirst($params['status']);
            }
        }

        // Date Range Filter
        if (!empty($params['date_range'])) {
            switch ($params['date_range']) {
                case 'this_week':
                    $sql .= " AND YEARWEEK(wp.start_date, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'this_month':
                    $sql .= " AND MONTH(wp.start_date) = MONTH(CURDATE()) AND YEAR(wp.start_date) = YEAR(CURDATE())";
                    break;
                case 'last_month':
                    $sql .= " AND MONTH(wp.start_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(wp.start_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
                    break;
            }
        }

        $sql .= " ORDER BY wp.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /**
     * Automatically mark expired workout plans as completed
     */
    public static function updateExpiredPlans()
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "UPDATE workout_plans SET status = 'Completed' 
             WHERE status = 'Active' AND end_date < CURDATE()"
        );
        return $stmt->execute();
    }

    /**
     * Update workout plan status
     */
    public static function updateStatus($id, $status)
    {
        $validStatuses = ['Active', 'Completed', 'Cancelled', 'Draft'];
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid status. Must be one of: ' . implode(', ', $validStatuses));
        }

        $db = Database::connect();
        $stmt = $db->prepare("UPDATE workout_plans SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}