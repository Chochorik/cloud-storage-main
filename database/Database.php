<?php

namespace DB;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $this->pdo = new \PDO("mysql:host=127.0.0.1;dbname=users;charset=utf8', 'root', 'root'");
        } catch (\PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public static function getInstance()
    {
        if (isset(self::$instance)) {
            self::$instance = new Database();
        }

        return self::$instance;
    }
}