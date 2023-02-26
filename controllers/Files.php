<?php

namespace controllers;

class Files
{
    private $connection;

    const PATH_TO_STORAGE = './storage/'; // путь до папки, где хранятся все папки и файлы пользователей

    public function __construct()
    {
        try {
            $this->connection = new \PDO('mysql:host=127.0.0.1;dbname=users;charset=utf8', 'root', 'root');
        } catch (\PDOException $exception) {
            echo json_encode($exception->getMessage());
            die();
        }
    }

    // получение списка файлов
    public function getFiles()
    {

    }

    // получение информации о конкретном файле
    public function getFileInfo(array $params)
    {

    }

    // добавление файла
    public function createFile()
    {

    }

    // изменение файла
    public function updateFile(array $params)
    {

    }

    // удаление файла
    public function deleteFile(array $params)
    {

    }
}