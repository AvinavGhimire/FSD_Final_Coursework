<?php
/**
 * Get the application base path dynamically.
 * Works with both local (/genz/public) and server deployments (/~np02cs4a240013/public)
 */
function getBasePath()
{
    if (isset($_SERVER['APP_BASE_PATH']) && !empty($_SERVER['APP_BASE_PATH'])) {
        return $_SERVER['APP_BASE_PATH'];
    }
    
    // Detect base path from SCRIPT_NAME
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = dirname($scriptName);
    
    // Normalize the path
    if ($basePath === DIRECTORY_SEPARATOR || $basePath === '\\') {
        $basePath = '';
    }
    
    $basePath = str_replace('\\', '/', $basePath);
    
    return $basePath;
}

/**
 * Generate a URL with the correct base path
 * @param string $path The path relative to the public directory
 * @return string The full URL path
 */
function url($path = '/')
{
    $basePath = getBasePath();
    
    // Ensure path starts with /
    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }
    
    return $basePath . $path;
}

/**
 * Get asset URL (CSS, JS, images, etc.)
 * @param string $assetPath The asset path relative to assets directory
 * @return string The full asset URL
 */
function asset($assetPath)
{
    $basePath = getBasePath();
    
    // Ensure asset path doesn't start with /
    if (str_starts_with($assetPath, '/')) {
        $assetPath = substr($assetPath, 1);
    }
    
    return $basePath . '/assets/' . $assetPath;
}
?>
