<?php

namespace controllers;

class Directories extends Files {
    // создание папки
    public function createDir()
    {
        /*session_start();

        if (!$this->checkAuth($_SESSION)) {
            $response = [
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ];

            echo json_encode($response);

            die(http_response_code(403));
        }

        $file = json_decode(file_get_contents('php://input'), true);

        $dirName = $file['dirName'];
        $userName = $_SESSION['user'];

        $storagePath = self::PATH_TO_STORAGE . $userName . '/';

        if (mkdir($storagePath . $dirName)) {
            $response = [
                "status" => true,
                "message" => 'Папка была успешно создана'
            ];

            json_encode($response);
        }*/
    }

    // обновление информации папки
    public function updateDir(array $params)
    {

    }

    // получение информации конкретной папки
    public function getDirInfo(array $params)
    {
        session_start();

//        if (!$this->checkAuth($_SESSION)) {
//            $response = [
//                "status" => false,
//                "message" => 'Необходимо авторизоваться!'
//            ];
//
//            echo json_encode($response);
//            die(http_response_code(403));
//        }

        echo json_encode($params);
    }

    // удаление папки
    public function deleteDir(array $params)
    {

    }

    // информация корневой папки
    public function rootDirInfo()
    {
        session_start();

        if (!$this->checkAuth($_SESSION)) {
            $response = [
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        $filesList = scandir(self::PATH_TO_STORAGE . $_SESSION['user']);
        $dirArray = [];
        $filesArray = [];

        unset($filesList[0]);
        unset($filesList[1]);

        foreach ($filesList as $item) {
            if (is_dir(self::PATH_TO_STORAGE . $_SESSION['user'] . '/' . $item)) {
                $dirArray[] = $item;
                continue;
            }

            $filesArray[] = $item;
        }

        $response = [
            "status" => true,
            "dirArray" => $dirArray,
            "filesArray" => $filesArray,
            "path" => self::PATH_TO_STORAGE . $_SESSION['user'] . '/'
        ];

        echo json_encode($response);
    }
}