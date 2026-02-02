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

    public function create()
    {
        $members = Member::all();
        $this->view('memberships/create', compact('members'));
    }

    public function store()
    {
        $data = [
            'member_id' => $_POST['member_id'] ?? '',
            'membership_type' => $_POST['membership_type'] ?? '',
            'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date' => $_POST['end_date'] ?? '',
            'price' => $_POST['price'] ?? 0
        ];

        // Validate required fields
        if (empty($data['member_id']) || empty($data['membership_type']) || empty($data['end_date'])) {
            $_SESSION['error'] = 'All required fields must be filled!';
            $this->redirect('/memberships/create');
            return;
        }

        try {
            if (Membership::create($data)) {
                $_SESSION['success'] = 'Membership created successfully!';
                $this->redirect('/memberships');
            } else {
                $_SESSION['error'] = 'Failed to create membership!';
                $this->redirect('/memberships/create');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            $this->redirect('/memberships/create');
        }
    }
}
