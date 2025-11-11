<?php
session_start();
define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/Backend/config/database.php';
$config = require BASE_PATH . '/Backend/config/config.php';