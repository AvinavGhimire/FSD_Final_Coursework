<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\WorkoutPlan;

use App\Models\Membership;

class DashboardController extends Controller
{
    public function index()
    {

        try {
            $data = [
                'member_stats' => Member::getStats(),
                'trainer_stats' => Trainer::getStats(),
                'workout_plan_stats' => WorkoutPlan::getStats(),

                'membership_stats' => Membership::getMembershipStats(),
                'recent_members' => array_slice(Member::all(), 0, 5),

                'expiring_memberships' => Membership::getExpiringMemberships(7)
            ];

            $this->view('dashboard/index', $data);
        } catch (Exception $e) {
            echo "Error in DashboardController@index: " . $e->getMessage() . "<br>";
            echo "Stack trace: " . $e->getTraceAsString() . "<br>";
        }
    }
}