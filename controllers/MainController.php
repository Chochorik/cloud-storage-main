<?php

namespace controllers;

class MainController {
    public function index()
    {
        require_once './pages/main.php';
    }

    public function registration()
    {
        require_once './pages/registration.html';
    }

    public function authorization()
    {
        require_once './pages/auth.php';
    }

    public function recoverPassword()
    {
        require_once './pages/recovery.html';
    }

    public function notFound()
    {
        require_once './pages/error.html';
    }
}