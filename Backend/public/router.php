<?php
$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/':
    case '/index.php':
        require 'index.php';
        break;
    case '/welcome':
        require 'welcome.php';
        break;
    case '/admin':
        require 'admin.php';
        break;
    default:
        http_response_code(404);
        echo "Page non trouvée.";
        break;
}