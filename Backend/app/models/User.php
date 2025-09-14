<?php
class User {
    public $id;
    public $username;
    public $password_hash;
    public $role;
    public $profil;
    public $email;
    public $phone;
    public $quiz_score;

    public function __construct($id, $username, $password_hash, $role = 'user' 'admin', $profil = 'novice' 'confirmé' 'expert', $email = null, $phone = null, $quiz_score = 0) {
        $this->id = $id;
        $this->username = $username;
        $this->password_hash = $password_hash;
        $this->role = $role;
        $this->profil = $profil;
        $this->email = $email;
        $this->phone = $phone;
        $this->quiz_score = $quiz_score;
    }
}
