<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Membership
{
    public static function getTypes()
    {
        return ['Basic', 'Standard', 'Premium'];
    }

    public static function getStatuses()
    {
        return ['Active', 'Expired', 'Suspended'];
    }

    public static function validateMembership($memberId)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT id, first_name, last_name, membership_type, 
             membership_start_date, membership_expiry_date, status
             FROM members WHERE id = ?"
        );
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();

        if (!$member) {
            return ['valid' => false, 'message' => 'Member not found'];
        }

        $today = date('Y-m-d');
        $expiryDate = $member['membership_expiry_date'];

        if ($member['status'] !== 'Active') {
            return [
                'valid' => false, 
                'message' => 'Membership is ' . strtolower($member['status']),
                'member' => $member
            ];
        }

        if ($expiryDate < $today) {
            // Auto-update status to expired
            $updateStmt = $db->prepare("UPDATE members SET status = 'Expired' WHERE id = ?");
            $updateStmt->execute([$memberId]);
            
            return [
                'valid' => false, 
                'message' => 'Membership expired on ' . date('M d, Y', strtotime($expiryDate)),
                'member' => $member
            ];
        }

        $daysUntilExpiry = (strtotime($expiryDate) - strtotime($today)) / (60 * 60 * 24);

        if ($daysUntilExpiry <= 7) {
            return [
                'valid' => true, 
                'message' => 'Membership expires in ' . ceil($daysUntilExpiry) . ' days',
                'warning' => true,
                'member' => $member
            ];
        }

        return [
            'valid' => true, 
            'message' => 'Membership is valid until ' . date('M d, Y', strtotime($expiryDate)),
            'member' => $member
        ];
    }

    public static function renewMembership($memberId, $months = 1)
    {
        $db = Database::connect();
        
        // Get current member data
        $stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();

        if (!$member) {
            return false;
        }

        // Calculate new expiry date
        $currentExpiry = $member['membership_expiry_date'];
        $today = date('Y-m-d');
        
        // If membership is already expired, start from today
        $startDate = ($currentExpiry < $today) ? $today : $currentExpiry;
        $newExpiryDate = date('Y-m-d', strtotime($startDate . " +$months months"));

        // Update membership
        $updateStmt = $db->prepare(
            "UPDATE members SET membership_expiry_date = ?, status = 'Active' WHERE id = ?"
        );
        
        return $updateStmt->execute([$newExpiryDate, $memberId]);
    }

    public static function getExpiringMemberships($days = 30)
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT * FROM members 
             WHERE membership_expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) 
             AND status = 'Active' 
             ORDER BY membership_expiry_date ASC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public static function getMembershipStats()
    {
        $db = Database::connect();
        $stats = [];
        
        // Total by type
        $typeStats = $db->query(
            "SELECT membership_type, COUNT(*) as count 
             FROM members 
             WHERE status = 'Active'
             GROUP BY membership_type"
        )->fetchAll();
        
        $stats['by_type'] = [];
        foreach ($typeStats as $type) {
            $stats['by_type'][$type['membership_type']] = $type['count'];
        }
        
        // Expiring soon (next 7 days)
        $stats['expiring_7_days'] = $db->query(
            "SELECT COUNT(*) as count FROM members 
             WHERE membership_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
             AND membership_expiry_date >= CURDATE()
             AND status = 'Active'"
        )->fetch()['count'];
        
        // Expiring soon (next 30 days)
        $stats['expiring_30_days'] = $db->query(
            "SELECT COUNT(*) as count FROM members 
             WHERE membership_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
             AND membership_expiry_date >= CURDATE()
             AND status = 'Active'"
        )->fetch()['count'];
        
        // Already expired but not updated
        $stats['expired'] = $db->query(
            "SELECT COUNT(*) as count FROM members 
             WHERE membership_expiry_date < CURDATE() 
             AND status = 'Active'"
        )->fetch()['count'];

        return $stats;
    }
}
