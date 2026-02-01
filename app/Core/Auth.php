<?php

namespace App\Core;

class Auth
{
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function login($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;
    }

    public static function logout()
    {
        $_SESSION = [];
        session_destroy();
        self::startSession();
    }

    public static function check()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function user()
    {
        if (self::check()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email']
            ];
        }
        return null;
    }

    public static function csrfToken()
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    public static function verifyCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireLogin()
    {
        if (!self::check()) {
            $basePath = $_SERVER['APP_BASE_PATH'] ?? '';
            header('Location: ' . $basePath . '/login');
            exit;
        }
    }
}
