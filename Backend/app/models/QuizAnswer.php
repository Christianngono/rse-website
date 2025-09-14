<?php
class QuizAnswer {
    public $id;
    public $user_name;
    public $question_id;
    public $user_answer;
    public $is_correct;
    public $answered_at;

    public function __construct($id, $user_name, $question_id, $user_answer, $is_correct, $answered_at) {
        $this->id = $id;
        $this->user_name = $user_name;
        $this->question_id = $question_id;
        $this->user_answer = $user_answer;
        $this->is_correct = $is_correct;
        $this->answered_at = $answered_at;
    }
}