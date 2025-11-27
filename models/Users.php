<?php
class Users
{
    private $file = "data/users.json";
    private $users = [];

    public function __construct()
    {
        if (file_exists($this->file)) {
            $data = file_get_contents($this->file);
            $this->users = json_decode($data, true) ?? [];
        }
    }

    public function getAll()
    {
        return $this->users;
    }

    public function findByEmail($email) {
        foreach ($this->users as $user) {
            if ($user['email'] === $email && !$user['deleted']) {
                return $user;
            }
        }
        return null;
    }

    public function findById($id)
    {
        foreach ($this->users as $user) {
            if ($user['id'] === $id && !$user['deleted']) {
                return $user;
            }
        }
        return null;
    }

    public function save()
    {
        file_put_contents($this->file, json_encode($this->users, JSON_PRETTY_PRINT));
    }

    public function add($data) {
        if ($this->findByEmail($data['email'])) {
            return false; // Email already exists
        }
        $data['id'] = uniqid();
        $data['deleted'] = false;
        $this->users[] = $data;
        $this->save();
        return true;
    }

    public function update($id, $newData)
    {
        foreach ($this->users as &$user) {
            if ($user['id'] === $id && !$user['deleted']) {
                if (isset($newData['email']) && $newData['email'] !== $user['email']) {
                    if ($this->findByEmail($newData['email'])) {
                        return false; // New email already in use
                    }
                    $user['email'] = $newData['email'];
                }
                if (isset($newData['name'])) {
                    $user['name'] = $newData['name'];
                }
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function delete($id)
    {
        // Soft delete
        foreach ($this->users as &$user) {
            if ($user['id'] === $id && !$user['deleted']) {
                $user['deleted'] = true;
                $this->save();
                return true;
            }
        }
        return false;
    }
}
