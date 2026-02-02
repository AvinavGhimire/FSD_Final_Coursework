<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\WorkoutPlan;
use App\Models\Member;
use App\Models\Trainer;
use Exception;

class WorkoutPlanController extends Controller
{
    public function index()
    {
        // Update any expired plans to completed status first
        WorkoutPlan::updateExpiredPlans();
        
        $filters = [
            'search' => $_GET['search'] ?? null,
            'trainer_id' => $_GET['trainer_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'date_range' => $_GET['date_range'] ?? null
        ];

        // Check if any filter is active
        if (array_filter($filters)) {
            $workoutPlans = WorkoutPlan::search($filters);
        } else {
            $workoutPlans = WorkoutPlan::all();
        }

        $trainers = Trainer::getActive();
        $stats = WorkoutPlan::getStats();

        $this->view('workout-plans/index', [
            'workoutPlans' => $workoutPlans,
            'trainers' => $trainers,
            'stats' => $stats,
            'searchTerm' => $filters['search'],
            'selectedTrainer' => $filters['trainer_id'],
            'selectedStatus' => $filters['status'],
            'selectedDateRange' => $filters['date_range'],
            'totalPlans' => count($workoutPlans)
        ]);
    }

    public function create()
    {
        $members = Member::all();
        $trainers = Trainer::getActive();

        // Handle selected member/trainer if passed via GET
        $selectedMember = $_GET['member_id'] ?? null;
        $selectedTrainer = $_GET['trainer_id'] ?? null;

        $this->view('workout-plans/create', compact('members', 'trainers', 'selectedMember', 'selectedTrainer'));
    }

    public function store()
    {
        header('Content-Type: application/json');

        try {
            // Validation
            $errors = $this->validateWorkoutPlan($_POST);
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                return;
            }

            $planId = WorkoutPlan::create($_POST);
            if ($planId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Workout plan created successfully!',
                    'plan_id' => $planId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create workout plan!']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function validateWorkoutPlan($data)
    {
        $errors = [];

        if (empty($data['plan_name'])) {
            $errors['plan_name'] = 'Plan name is required';
        }

        if (empty($data['plan_type'])) {
            $errors['plan_type'] = 'Plan type is required';
        }

        if (empty($data['member_id'])) {
            $errors['member_id'] = 'Member selection is required';
        }

        if (empty($data['trainer_id'])) {
            $errors['trainer_id'] = 'Trainer selection is required';
        }

        if (empty($data['start_date'])) {
            $errors['start_date'] = 'Start date is required';
        }

        if (empty($data['end_date'])) {
            $errors['end_date'] = 'End date is required';
        } else if (!empty($data['start_date']) && $data['end_date'] <= $data['start_date']) {
            $errors['end_date'] = 'End date must be after start date';
        }

        if (empty($data['sessions_per_week'])) {
            $errors['sessions_per_week'] = 'Sessions per week is required';
        }

        if (empty($data['session_duration'])) {
            $errors['session_duration'] = 'Session duration is required';
        }

        return $errors;
    }

    public function show()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/workout-plans');
        }

        $workoutPlan = WorkoutPlan::find($id);
        if (!$workoutPlan) {
            $_SESSION['error'] = 'Workout plan not found!';
            $this->redirect('/workout-plans');
        }

        $this->view('workout-plans/view', compact('workoutPlan'));
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/workout-plans');
        }

        $workoutPlan = WorkoutPlan::find($id);
        if (!$workoutPlan) {
            $_SESSION['error'] = 'Workout plan not found!';
            $this->redirect('/workout-plans');
        }

        $members = Member::all();
        $trainers = Trainer::getActive();

        $this->view('workout-plans/edit', compact('workoutPlan', 'members', 'trainers'));
    }

    public function update()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->redirect('/workout-plans');
        }

        try {
            if (WorkoutPlan::update($id, $_POST)) {
                $_SESSION['success'] = 'Workout plan updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update workout plan!';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $this->redirect('/workout-plans/view?id=' . $id);
    }

    public function updateStatus()
    {
        header('Content-Type: application/json');
        
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (!$id || !$status) {
            echo json_encode(['success' => false, 'message' => 'ID and status are required']);
            return;
        }

        try {
            if (WorkoutPlan::updateStatus($id, $status)) {
                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid workout plan ID']);
            return;
        }

        try {
            if (WorkoutPlan::delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Workout plan deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete workout plan']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}