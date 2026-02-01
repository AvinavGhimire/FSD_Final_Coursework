<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Membership;
use App\Models\Member;

class MembershipController extends Controller
{
    public function index()
    {
        $expiringMemberships = Membership::getExpiringMemberships(30);
        $membershipStats = Membership::getMembershipStats();

        $this->view('memberships/index', compact('expiringMemberships', 'membershipStats'));
    }

    public function expiring()
    {
        $days = $_GET['days'] ?? 30;
        $expiringMemberships = Membership::getExpiringMemberships($days);
        $membershipStats = Membership::getMembershipStats();

        $this->view('memberships/index', compact('expiringMemberships', 'days', 'membershipStats'));
    }

    public function validateAjax()
    {
        header('Content-Type: application/json');

        $memberId = $_GET['member_id'] ?? null;

        if (!$memberId) {
            echo json_encode(['success' => false, 'message' => 'Member ID is required']);
            return;
        }

        try {
            $validation = Membership::validateMembership($memberId);
            echo json_encode(['success' => true, 'data' => $validation]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function renew()
    {
        $memberId = $_POST['member_id'] ?? null;
        $months = $_POST['months'] ?? 1;

        if (!$memberId) {
            $_SESSION['error'] = 'Member ID is required!';
            $this->redirect('/memberships');
        }

        try {
            if (Membership::renewMembership($memberId, $months)) {
                $_SESSION['success'] = 'Membership renewed successfully!';
            } else {
                $_SESSION['error'] = 'Failed to renew membership!';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $this->redirect('/members/view?id=' . $memberId);
    }
}
