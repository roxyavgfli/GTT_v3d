<?php
namespace main\dbBundle\modals;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Login
 *
 * @author aymvi_000
 */
class Login {

    private $mail;
    private $username;
    private $name;
    private $surname;
    private $password;
    private $group;
    private $permission;

    public function getUsername() {
        return $this->username;
    }

    public function getName() {
        return $this->name;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getGroup() {
        return $this->group;
    }

    public function getPermission() {
        return $this->permission;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setSurname($surname) {
        $this->surname = $surname;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setGroup($group) {
        $this->group = $group;
    }

    public function setPermission($permission) {
        $this->permission = $permission;
    }

    public function getMail() {
        return $this->mail;
    }

    public function setMail($mail) {
        $this->mail = $mail;
    }


}
