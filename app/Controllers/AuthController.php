<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login');
    }

    public function login()
    {
        // CSRF Check
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->view('auth/login', ['error' => 'Invalid CSRF token']);
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            Auth::login($user);
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', ['error' => 'Invalid email or password', 'email' => $email]);
    }

    public function logout()
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
