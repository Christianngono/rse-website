<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$config = $pdo->query("SELECT email, frequency, format FROM report_config WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
echo json_encode($config ?: []);