<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Attendance;
use App\Models\WorkoutPlan;
use \Exception; // Import Exception class

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::all();
        $membershipTypes = Membership::getTypes();
        $membershipStatuses = Membership::getStatuses();

        $this->view('members/index', [
            'members' => $members,
            'membershipTypes' => $membershipTypes,
            'membershipStatuses' => $membershipStatuses
        ]);
    }

    public function create()
    {
        $membershipTypes = Membership::getTypes();
        $membershipStatuses = Membership::getStatuses();

        $this->view('members/create', [
            'membershipTypes' => $membershipTypes,
            'membershipStatuses' => $membershipStatuses
        ]);
    }

    public function store()
    {
        try {
            if (!Member::validateEmail($_POST['email'])) {
                $_SESSION['error'] = 'Email already exists!';
                $this->redirect('/members/create');
            }

            if (Member::create($_POST)) {
                $_SESSION['success'] = 'Member created successfully!';
            } else {
                $_SESSION['error'] = 'Failed to create member!';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $this->redirect('/members');
    }

    public function show()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/members');
        }

        $member = Member::find($id);
        if (!$member) {
            $_SESSION['error'] = 'Member not found!';
            $this->redirect('/members');
        }

        // Fetch workout plans
        $workoutPlans = WorkoutPlan::getByMember($id);

        // Get membership history/stats if available, otherwise just pass member
        $this->view('members/view', [
            'member' => $member,
            'workoutPlans' => $workoutPlans
        ]);
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/members');
        }

        $member = Member::find($id);
        if (!$member) {
            $_SESSION['error'] = 'Member not found!';
            $this->redirect('/members');
        }

        $membershipTypes = Membership::getTypes();
        $membershipStatuses = Membership::getStatuses();

        $this->view('members/edit', compact('member', 'membershipTypes', 'membershipStatuses'));
    }

    public function update()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->redirect('/members');
        }

        try {
            if (!Member::validateEmail($_POST['email'], $id)) {
                $_SESSION['error'] = 'Email already exists!';
                $this->redirect('/members/edit?id=' . $id);
            }

            if (Member::update($id, $_POST)) {
                $_SESSION['success'] = 'Member updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update member!';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $this->redirect('/members/view?id=' . $id);
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
            return;
        }

        try {
            if (Member::delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Member deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete member']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function search()
    {
        $params = $_GET;
        $members = Member::search($params);
        $membershipTypes = Membership::getTypes();
        $membershipStatuses = Membership::getStatuses();

        $this->view('members/index', compact('members', 'membershipTypes', 'membershipStatuses', 'params'));
    }

    public function searchAjax()
    {
        header('Content-Type: application/json');

        try {
            $params = $_GET;
            $members = Member::search($params);
            echo json_encode(['success' => true, 'data' => $members]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
