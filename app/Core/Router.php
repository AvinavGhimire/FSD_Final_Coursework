<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Router
{
    protected $routes = [];
    protected $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader, [
            'cache' => false, // Disable cache for development
            'debug' => true
        ]);
    }

    public function get($uri, $action)
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post($uri, $action)
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch($uri, $method)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Get base path from server variable
        $basePath = $_SERVER['APP_BASE_PATH'] ?? '';
        
        // Strip base path from the beginning if present
        if (!empty($basePath) && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Normalize path
        if ($path === '' || $path === null) {
            $path = '/';
        }
        
        // Remove query string if present
        if (strpos($path, '?') !== false) {
            $path = substr($path, 0, strpos($path, '?'));
        }

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo $this->twig->render('errors/404.twig', [
                'title' => '404 - Page Not Found',
                'uri' => $uri,
                'method' => $method,
                'requested_path' => $path
            ]);
            return;
        }

        [$controller, $methodName] = explode('@', $this->routes[$method][$path]);

        $controller = "App\\Controllers\\$controller";
        
        if (!class_exists($controller)) {
            http_response_code(500);
            echo $this->twig->render('errors/500.twig', [
                'title' => '500 - Server Error',
                'message' => "Controller not found: $controller"
            ]);
            return;
        }

        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $methodName)) {
            http_response_code(500);
            echo $this->twig->render('errors/500.twig', [
                'title' => '500 - Server Error',
                'message' => "Method not found: $methodName in controller $controller"
            ]);
            return;
        }

        call_user_func([$controllerInstance, $methodName]);
    }
}
