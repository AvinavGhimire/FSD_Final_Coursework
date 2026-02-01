<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Member
{
    public static function all()
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM members ORDER BY created_at DESC")->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function search($params)
    {
        $db = Database::connect();
        $sql = "SELECT * FROM members WHERE 1=1";
        $bindings = [];

        if (!empty($params['name'])) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
            $bindings[] = '%' . $params['name'] . '%';
            $bindings[] = '%' . $params['name'] . '%';
        }

        if (!empty($params['membership_type'])) {
            $sql .= " AND membership_type = ?";
            $bindings[] = $params['membership_type'];
        }

        if (!empty($params['status'])) {
            $sql .= " AND status = ?";
            $bindings[] = $params['status'];
        }

        if (!empty($params['expiry_date'])) {
            $sql .= " AND membership_expiry_date <= ?";
            $bindings[] = $params['expiry_date'];
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
            "INSERT INTO members (first_name, last_name, email, phone, membership_type, 
             membership_start_date, membership_expiry_date, status, address, date_of_birth, 
             emergency_contact_name, emergency_contact_phone) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['membership_type'],
            !empty($data['membership_start_date']) ? $data['membership_start_date'] : null,
            !empty($data['membership_expiry_date']) ? $data['membership_expiry_date'] : null,
            $data['status'] ?? 'Active',
            $data['address'] ?? null,
            !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            $data['emergency_contact_name'] ?? null,
            $data['emergency_contact_phone'] ?? null
        ]);
    }

    public static function update($id, $data)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?, 
             membership_type = ?, membership_start_date = ?, membership_expiry_date = ?, 
             status = ?, address = ?, date_of_birth = ?, emergency_contact_name = ?, 
             emergency_contact_phone = ? WHERE id = ?"
        );

        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['membership_type'],
            !empty($data['membership_start_date']) ? $data['membership_start_date'] : null,
            !empty($data['membership_expiry_date']) ? $data['membership_expiry_date'] : null,
            $data['status'],
            $data['address'] ?? null,
            !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            $data['emergency_contact_name'] ?? null,
            $data['emergency_contact_phone'] ?? null,
            $id
        ]);
    }

    public static function delete($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM members WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getExpiringMemberships($days = 30)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT * FROM members 
             WHERE membership_expiry_date <= DATE_ADD(NOW(), INTERVAL ? DAY) 
             AND status = 'Active' 
             ORDER BY membership_expiry_date ASC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public static function getByMembershipType($type)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM members WHERE membership_type = ?");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public static function getStats()
    {
        $db = Database::connect();
        $stats = [];

        // Total members
        $stats['total'] = $db->query("SELECT COUNT(*) as count FROM members")->fetch()['count'];

        // Active members
        $stats['active'] = $db->query("SELECT COUNT(*) as count FROM members WHERE status = 'Active'")->fetch()['count'];

        // Expired members
        $stats['expired'] = $db->query("SELECT COUNT(*) as count FROM members WHERE status = 'Expired'")->fetch()['count'];

        // Expiring soon (next 30 days)
        $stats['expiring_soon'] = $db->query(
            "SELECT COUNT(*) as count FROM members 
             WHERE membership_expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) 
             AND status = 'Active'"
        )->fetch()['count'];

        return $stats;
    }

    public static function validateEmail($email, $excludeId = null)
    {
        $db = Database::connect();
        $sql = "SELECT COUNT(*) as count FROM members WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] == 0;
    }
}
