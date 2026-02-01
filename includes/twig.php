<?php
/**
 * Twig template engine bootstrap.
 * Separates UI (templates) from PHP logic.
 */

$baseDir = dirname(__DIR__);

if (!is_file($baseDir . '/vendor/autoload.php')) {
    die('Please run <code>composer install</code> in the project root.');
}

require_once $baseDir . '/vendor/autoload.php';
require_once $baseDir . '/includes/functions.php';

$loader = new \Twig\Loader\FilesystemLoader($baseDir . '/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'auto_reload' => true,
]);

$twig->addFunction(new \Twig\TwigFunction('formatDate', 'formatDate'));
$twig->addFunction(new \Twig\TwigFunction('isMembershipExpired', 'isMembershipExpired'));

/**
 * Render a Twig template with data and output HTML.
 *
 * @param string $template Template name (e.g. 'index.twig')
 * @param array  $data     Data to pass to the template
 */
function render($template, array $data = []) {
    global $twig;
    echo $twig->render($template, $data);
}
