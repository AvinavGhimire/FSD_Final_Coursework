<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Core\Auth;
use \Exception; // Import Exception class

class Controller
{
    protected $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader);
    }

    protected function view($view, $data = [])
    {
        try {
            // Add global auth data
            $data['auth'] = [
                'check' => Auth::check(),
                'user' => Auth::user(),
                'csrf_token' => Auth::csrfToken()
            ];
            
            // Add base path for URL generation
            $data['base_path'] = $_SERVER['APP_BASE_PATH'] ?? '';

            // Detect current page for Map Bar active state
            $parts = explode('/', $view);
            $data['current_page'] = $parts[0];

            echo $this->twig->render($view . '.twig', $data);
        } catch (Exception $e) {
            echo "Error rendering view: " . $e->getMessage() . "<br>";
            echo "Stack trace: " . $e->getTraceAsString() . "<br>";
        }
    }
    
    protected function redirect($path)
    {
        $basePath = $_SERVER['APP_BASE_PATH'] ?? '';
        header('Location: ' . $basePath . $path);
        exit;
    }
}
