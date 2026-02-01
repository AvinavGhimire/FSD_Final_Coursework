<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Trainer
{
    public static function all()
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM trainers ORDER BY created_at DESC")->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM trainers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getActive()
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM trainers WHERE status = 'Active' ORDER BY first_name, last_name")->fetchAll();
    }

    public static function search($params)
    {
        $db = Database::connect();
        $sql = "SELECT * FROM trainers WHERE 1=1";
        $bindings = [];

        if (!empty($params['name'])) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
            $bindings[] = '%' . $params['name'] . '%';
            $bindings[] = '%' . $params['name'] . '%';
        }

        if (!empty($params['specialization'])) {
            $sql .= " AND specialization LIKE ?";
            $bindings[] = '%' . $params['specialization'] . '%';
        }

        if (!empty($params['status'])) {
            $sql .= " AND status = ?";
            $bindings[] = $params['status'];
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public static function create($data)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO trainers (first_name, last_name, email, phone, specialization, 
             experience_years, certification, hire_date, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['specialization'] ?? null,
            $data['experience_years'] ?? null,
            $data['certification'] ?? null,
            $data['hire_date'],
            $data['status'] ?? 'Active'
        ]);
    }

    public static function update($id, $data)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "UPDATE trainers SET first_name = ?, last_name = ?, email = ?, phone = ?, 
             specialization = ?, experience_years = ?, certification = ?, hire_date = ?, 
             status = ? WHERE id = ?"
        );

        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['specialization'],
            $data['experience_years'],
            $data['certification'],
            !empty($data['hire_date']) ? $data['hire_date'] : null,
            $data['status'],
            $id
        ]);
    }

    public static function delete($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM trainers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getStats()
    {
        $db = Database::connect();
        $stats = [];

        // Total trainers
        $stats['total'] = $db->query("SELECT COUNT(*) as count FROM trainers")->fetch()['count'];

        // Active trainers
        $stats['active'] = $db->query("SELECT COUNT(*) as count FROM trainers WHERE status = 'Active'")->fetch()['count'];

        // Inactive trainers
        $stats['inactive'] = $db->query("SELECT COUNT(*) as count FROM trainers WHERE status = 'Inactive'")->fetch()['count'];

        return $stats;
    }

    public static function validateEmail($email, $excludeId = null)
    {
        $db = Database::connect();
        $sql = "SELECT COUNT(*) as count FROM trainers WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] == 0;
    }

    public static function getWorkloadStats()
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT t.id, t.first_name, t.last_name, 
             COUNT(wp.id) as active_plans,
             AVG(wp.duration_weeks) as avg_duration
             FROM trainers t
             LEFT JOIN workout_plans wp ON t.id = wp.trainer_id AND wp.status = 'Active'
             WHERE t.status = 'Active'
             GROUP BY t.id, t.first_name, t.last_name
             ORDER BY active_plans DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
