<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/path-helpers.php';

use App\Core\Router;
use App\Core\Auth;

// Start session
Auth::startSession();

// Initialize router
$router = new Router();

// Auth routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Public routes whitelist
$publicRoutes = ['/login', '/logout'];

// Check auth for non-public routes
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Dynamically detect and store base path
$basePath = getBasePath();
$_SERVER['APP_BASE_PATH'] = $basePath;

// Normalize URI
if (!empty($basePath) && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
if ($uri === '' || $uri === '/') {
    $uri = '/';
}

// Simple protection logic
$isPublic = false;
foreach ($publicRoutes as $route) {
    if (strpos($uri, $route) === 0) {
        $isPublic = true;
        break;
    }
}

if (!$isPublic && !Auth::check()) {
    header('Location: ' . $basePath . '/login');
    exit;
}

// Dashboard routes
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Member routes
$router->get('/members', 'MemberController@index');
$router->get('/members/create', 'MemberController@create');
$router->post('/members/store', 'MemberController@store');
$router->get('/members/edit', 'MemberController@edit');
$router->post('/members/update', 'MemberController@update');
$router->post('/members/delete', 'MemberController@delete');
$router->get('/members/search', 'MemberController@search');
$router->get('/members/view', 'MemberController@show');

// Trainer routes
$router->get('/trainers', 'TrainerController@index');
$router->get('/trainers/create', 'TrainerController@create');
$router->post('/trainers/store', 'TrainerController@store');
$router->get('/trainers/edit', 'TrainerController@edit');
$router->post('/trainers/update', 'TrainerController@update');
$router->post('/trainers/delete', 'TrainerController@delete');
$router->get('/trainers/view', 'TrainerController@show');

// Membership routes
$router->get('/memberships', 'MembershipController@index');
$router->get('/memberships/create', 'MembershipController@create');
$router->post('/memberships/store', 'MembershipController@store');
$router->get('/memberships/expiring', 'MembershipController@expiring');
$router->post('/memberships/renew', 'MembershipController@renew');

// Workout Plan routes
$router->get('/workout-plans', 'WorkoutPlanController@index');
$router->get('/workout-plans/create', 'WorkoutPlanController@create');
$router->post('/workout-plans/create', 'WorkoutPlanController@store');
$router->get('/workout-plans/edit', 'WorkoutPlanController@edit');
$router->post('/workout-plans/update', 'WorkoutPlanController@update');
$router->post('/workout-plans/update-status', 'WorkoutPlanController@updateStatus');
$router->post('/workout-plans/delete', 'WorkoutPlanController@delete');
$router->get('/workout-plans/view', 'WorkoutPlanController@show');

// Attendance routes
$router->get('/attendance', 'AttendanceController@index');
$router->get('/attendance/create', 'AttendanceController@create');
$router->post('/attendance/store', 'AttendanceController@store');

// Ajax routes
$router->get('/api/membership/validate', 'MembershipController@validateAjax');
$router->get('/api/members/search', 'MemberController@searchAjax');
$router->get('/api/trainers/search', 'TrainerController@searchAjax');

// Test routes
$router->get('/test/routes', 'TestController@routes');

// Dispatch request
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
