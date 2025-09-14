<?php
require_once(__DIR__ . '/../models/QuizAnswer.php');
require_once(__DIR__ . '/../config/database.php');

class QuizService {
    public static function saveAnswer($user_name, $question_id, $user_answer, $is_correct, $answered_at) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO user_answers (user_name, question_id, user_answer, is_correct, answered_at) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_name, $question_id, $user_answer, $is_correct, $answered_at]);
    }

    public static function getAnswersByUser($user_name) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM user_answers WHERE user_name = ?");
        $stmt->execute([$user_name]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'QuizAnswer');
    }
}