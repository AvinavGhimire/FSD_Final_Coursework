<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Trainer;
use App\Models\WorkoutPlan;
use Exception;

class TrainerController extends Controller
{
    public function index()
    {
        $trainers = Trainer::all();
        $workloadStats = Trainer::getWorkloadStats();
        
        $this->view('trainers/index', compact('trainers', 'workloadStats'));
    }

    public function create()
    {
        $this->view('trainers/create');
    }

    public function store()
    {
        try {
            // Validate email
            if (!Trainer::validateEmail($_POST['email'])) {
                $_SESSION['error'] = 'Email already exists!';
                $this->redirect('/trainers/create');
            }

            if (Trainer::create($_POST)) {
                $_SESSION['success'] = 'Trainer created successfully!';
            } else {
                $_SESSION['error'] = 'Failed to create trainer!';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $this->redirect('/trainers');
    }

    public function show()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Trainer ID is required.';
            $this->redirect('/trainers');
        }

        $trainer = Trainer::find($id);
        if (!$trainer) {
            $_SESSION['error'] = 'Trainer not found!';
            $this->redirect('/trainers');
        }

        $workoutPlans = WorkoutPlan::getByTrainer($id);

        $this->view('trainers/view', compact('trainer', 'workoutPlans'));
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Trainer ID is required.';
            $this->redirect('/trainers');
        }

        $trainer = Trainer::find($id);
        if (!$trainer) {
            $_SESSION['error'] = 'Trainer not found!';
            $this->redirect('/trainers');
        }

        $this->view('trainers/edit', compact('trainer'));
    }

    public function update()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Trainer ID is required.';
            $this->redirect('/trainers');
        }

        try {
            if (!Trainer::validateEmail($_POST['email'], $id)) {
                $_SESSION['error'] = 'Email already exists!';
                $this->redirect('/trainers/edit?id=' . $id);
            }

            if (Trainer::update($id, $_POST)) {
                $_SESSION['success'] = 'Trainer updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update trainer!';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $this->redirect('/trainers/view?id=' . $id);
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid trainer ID']);
            return;
        }

        try {
            if (Trainer::delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Trainer deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete trainer']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function searchAjax()
    {
        header('Content-Type: application/json');
        
        try {
            $params = $_GET;
            $trainers = Trainer::search($params);
            echo json_encode(['success' => true, 'data' => $trainers]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
