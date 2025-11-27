<?php
require_once "models/Users.php";

class UsersController {
    private $model;

    public function __construct() {
        $this->model = new Users();
    }

    public function addUser($name, $email) {
        return $this->model->add(['name' => $name, 'email' => $email]);
    }

    public function updateUser($id, $data) {
        return $this->model->update($id, $data);
    }

    public function deleteUser($id) {
        return $this->model->delete($id);
    }

    public function getUsers($includeDeleted = false) {
        $all = $this->model->getAll();
        if ($includeDeleted) {
            return $all;
        }
        // Only non-deleted users
        return array_filter(
            $all,
            function($user) { return !$user['deleted']; }
        );
    }
}
