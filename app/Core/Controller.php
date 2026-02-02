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
        
        // Add Twig functions for URL generation
        $this->twig->addFunction(new \Twig\TwigFunction('url', function($path = '/') {
            $basePath = $_SERVER['APP_BASE_PATH'] ?? '';
            if (!str_starts_with($path, '/')) {
                $path = '/' . $path;
            }
            return $basePath . $path;
        }));
        
        $this->twig->addFunction(new \Twig\TwigFunction('asset', function($assetPath) {
            $basePath = $_SERVER['APP_BASE_PATH'] ?? '';
            if (str_starts_with($assetPath, '/')) {
                $assetPath = substr($assetPath, 1);
            }
            return $basePath . '/assets/' . $assetPath;
        }));
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
