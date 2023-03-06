<?php

namespace controllers;

class Directories extends Files {
    // создание папки
    public function createDir()
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

        $user = $this->getUserId(session_id(), $_SESSION['user']);

        if (!$user['status']) {
            $response = [
                "status" => false,
                "message" => $user['message']
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        $userId = $user['id']; // id пользователя

        $dir = json_decode(file_get_contents('php://input'), true); // получаем данные с формы

        $dirName = $dir['dirName'];

        if (strlen($dirName) > 30) {
            $response = [
                "status" => false,
                "message" => 'Название слишком длинное!'
            ];

            echo json_encode($response);
            die();
        }

        $currentPath = $dir['path'];

        $pathToDir = $currentPath . $dirName . '/';

        $encodedName = $this->checkFileOrDirName($dirName);

        // подготовка к записи данных в БД
        $getPathId = $this->connection->prepare("SELECT `dir_id` FROM `directories` WHERE `path` = :path AND `user_id` = :userId");
        $getPathId->bindValue('path', $currentPath);
        $getPathId->bindValue('userId', $userId);

        $createNewDir = $this->connection->prepare("INSERT INTO `directories`(`path`, `name`, `user_id`) VALUES (:path, :dirName, :userId)");
        $createNewDir->bindValue('path', $pathToDir);
        $createNewDir->bindValue('dirName', $dirName);
        $createNewDir->bindValue('userId', $userId);

        $getDirId = $this->connection->prepare("SELECT `dir_id` FROM `directories` WHERE `path` = :path AND `user_id` = :userId");
        $getDirId->bindValue('path', $pathToDir);
        $getDirId->bindValue('userId', $userId);

        $createDirAtFiles = $this->connection->prepare("INSERT INTO `files`(`dir_id`, `type`, `encoded_name`, `real_name`, `belong_dir_id`, `id`) VALUES (:dirId, 'dir', :encodedName, :realName, :belongDir, :userId)");
        $createDirAtFiles->bindParam('dirId', $pathId);
        $createDirAtFiles->bindValue('encodedName', $encodedName);
        $createDirAtFiles->bindValue('realName', $dirName);
        $createDirAtFiles->bindParam('belongDir', $dirId);
        $createDirAtFiles->bindValue('userId', $userId);

        $checkSameDirName = $this->connection->prepare("SELECT * FROM `directories` WHERE `name` = :dirName AND `path` = :path AND `user_id` = :userId");
        $checkSameDirName->bindValue('dirName', $dirName);
        $checkSameDirName->bindValue('path', $pathToDir);
        $checkSameDirName->bindValue('userId', $userId);

        try {
            $this->connection->beginTransaction();

            $checkSameDirName->execute();
            $result = $checkSameDirName->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                $response = [
                    "status" => false,
                    "message" => 'Папка с таким именем в этом месте уже существует!'
                ];

                echo json_encode($response);
                die();
            }

            $getPathId->execute();
            $result = $getPathId->fetchAll(\PDO::FETCH_ASSOC);
            $pathId = $result[0]['dir_id'];

            $createNewDir->execute();

            $getDirId->execute();
            $result = $getDirId->fetchAll(\PDO::FETCH_ASSOC);
            $dirId = $result[0]['dir_id'];

            $createDirAtFiles->execute();

            $this->connection->commit();

            $response = [
                "status" => true,
                "message" => 'Папка была успешно создана'
            ];

            echo json_encode($response);
        } catch (\PDOException $exception) {
            $this->connection->rollBack();

            echo json_encode([
                "status" => false,
                "message" => $exception->getMessage()
            ]);
        }
    }

    // обновление информации папки
    public function updateDir(array $params)
    {

    }

    // получение информации конкретной папки
    public function getDirInfo(array $params)
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

        $user = $this->getUserId(session_id(), $_SESSION['user']);

        if (!$user['status']) {
            $response = [
                "status" => false,
                "message" => $user['message']
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        $userId = $user['id'];

        $dirId = $params[0];

        if ($dirId === 'root') {
            // получение информации о корневом каталоге
            $rootDirInfo = $this->connection->prepare("SELECT `directories`.`dir_id`, `directories`.`path` FROM `directories` WHERE `directories`.`user_id` = :userId AND `directories`.`name` = 'root'");
            $rootDirInfo->bindValue('userId', $userId);

            $rootDirInfo->execute();
            $dirInfo = $rootDirInfo->fetchAll(\PDO::FETCH_ASSOC);

            $response = [
                "status" => true,
                "rootDir" => $dirInfo
            ];

            echo json_encode($response);
            die();
        }

        $dirInfo = $this->connection->prepare("SELECT `files`.`file_id`, `files`.`encoded_name`, `files`.`real_name`, `files`.`type`, `files`.`belong_dir_id` FROM `files` WHERE `files`.`dir_id` = :dirId AND `files`.`id` = :userId");
        $dirInfo->bindValue('dirId', $dirId);
        $dirInfo->bindValue('userId', $userId);

        $dirInfo->execute();

        $result = $dirInfo->fetchAll(\PDO::FETCH_ASSOC);

        //получение пути папки
        $getDirPath = $this->connection->prepare("SELECT `path` FROM `directories` WHERE `dir_id` = :dirId AND `user_id` = :userId");
        $getDirPath->bindParam('dirId', $dirId);
        $getDirPath->bindValue('userId', $userId);

        $newFilesArray = [];
        $newDirsArray = [];

        foreach ($result as $item) {
            $fileType = $item['type'];
            $fileId = $item['file_id'];
            $realName = $item['real_name'];
            $dirId = $item['belong_dir_id'];

            if ($fileType === 'file') {
                $newFilesArray[] = [
                    "file_id" => $fileId
                ];

                continue;
            }

            if ($fileType === 'dir') {
                $getDirPath->execute();
                $dir = $getDirPath->fetchAll(\PDO::FETCH_ASSOC);

                $dirPath = $dir[0]['path'];

                $newDirsArray[] =[
                    "real_name" => $realName,
                    "dir_id" => $dirId,
                    "dir_path" => $dirPath
                ];
            }
        }

        $response = [
            "status" => true,
            "files_list" => $newFilesArray,
            "directories_list" => $newDirsArray
        ];

        echo json_encode($response);
    }

    // удаление папки
    public function deleteDir(array $params)
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

        $user = $this->getUserId(session_id(), $_SESSION['user']);

        if (!$user['status']) {
            $response = [
                "status" => false,
                "message" => $user['message']
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        $userId = $user['id']; // id пользователя
        $dirId = $params[0]; // id удаляемой папки

        // удаление всех файлов в папке перед удалением самой папки
        $getAllDirFiles = $this->connection->prepare("SELECT `encoded_name` FROM `files` WHERE `dir_id` = :dirId AND `id` = :userId");
        $getAllDirFiles->bindValue('dirId', $dirId);
        $getAllDirFiles->bindValue('userId', $userId);

        // удаление всех файлов удаляемой папки в таблице files
        $deleteFilesFromTable = $this->connection->prepare("DELETE FROM `files` WHERE `dir_id` = :dirId AND `id` = :userId");
        $deleteFilesFromTable->bindValue('dirId', $dirId);
        $deleteFilesFromTable->bindValue('userId', $userId);

        // удаление папки из таблицы files
        $deleteDirFromFiles = $this->connection->prepare("DELETE FROM `files` WHERE `belong_dir_id` = :dirId AND `id` = :userId");
        $deleteDirFromFiles->bindValue('dirId', $dirId);
        $deleteDirFromFiles->bindValue('userId', $userId);

        // удаление папки из таблицы directories
        $deleteDirFromDirs = $this->connection->prepare("DELETE FROM `directories` WHERE `dir_id` = :dirId AND `user_id` = :userId");
        $deleteDirFromDirs->bindValue('dirId', $dirId);
        $deleteDirFromDirs->bindValue('userId', $userId);

        try {
            $this->connection->beginTransaction();

            $getAllDirFiles->execute();
            $files = $getAllDirFiles->fetchAll(\PDO::FETCH_ASSOC);

            // удаление файлов из главного хранилища
            foreach ($files as $file) {
                $link = self::PATH_TO_STORAGE . $file['encoded_name'];

                unlink($link);
            }

            $deleteFilesFromTable->execute();
            $deleteDirFromFiles->execute();
            $deleteDirFromDirs->execute();

            $this->connection->commit();
        } catch (\PDOException $exception) {
            $this->connection->rollBack();

            $response = [
                "status" => false,
                "message" => $exception->getMessage()
            ];

            echo json_encode($response);
        }
    }
}