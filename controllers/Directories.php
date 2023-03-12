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

        $dirName = ltrim($dirName);
        $dirName = rtrim($dirName);

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

        $input = json_decode(file_get_contents('php://input'), true);

        $newDirName = $input['newDirName'];
        $oldDirName = $input['oldDirName'];
        $currentPath = $input['currentPath'];

        $newDirName = ltrim($newDirName);
        $newDirName = rtrim($newDirName);

        $userId = $user['id'];
        $dirId = $params[0];

        if ($newDirName === '') {
            $response = [
                "status" => false,
                "message" => 'Поле не должно быть пустым!'
            ];

            echo json_encode($response);
            die();
        }

        if ($newDirName === $oldDirName) { // проверка на совпадение нового и старого имени папки
            $response = [
                "status" => false,
                "message" => 'Новое имя не должно совпадать со старым!'
            ];

            echo json_encode($response);
            die();
        }

        $newPath = $currentPath . $newDirName . '/';

        // проверка на существование папки в данном месте с таким именем
        $checkDirName = $this->connection->prepare("SELECT * FROM `directories` WHERE `name` = :newName AND `user_id` = :userId AND `path` = :path");
        $checkDirName->bindValue('newName', $newDirName);
        $checkDirName->bindValue('userId', $userId);
        $checkDirName->bindValue('path', $newPath);

        // изменение имени папки и пути в таблице папок
        $renameDir = $this->connection->prepare("UPDATE `directories` SET `path` = :newPath, `name` = :newName WHERE `user_id` = :userId AND `dir_id` = :dirId");
        $renameDir->bindValue('newPath', $newPath);
        $renameDir->bindValue('newName', $newDirName);
        $renameDir->bindValue('userId', $userId);
        $renameDir->bindValue('dirId', $dirId);

        // изменение имени папки в таблице файлов
        $encodedNewDirName = $this->checkFileOrDirName($newDirName);

        $renameDirAtFiles = $this->connection->prepare("UPDATE `files` SET `encoded_name` = :newEncodedName, `real_name` = :newRealName WHERE `belong_dir_id` = :dirId AND `id` = :userId");
        $renameDirAtFiles->bindValue('newEncodedName', $encodedNewDirName);
        $renameDirAtFiles->bindValue('newRealName', $newDirName);
        $renameDirAtFiles->bindValue('dirId', $dirId);
        $renameDirAtFiles->bindValue('userId', $userId);

        try {
            $this->connection->beginTransaction();

            $checkDirName->execute();
            $result = $checkDirName->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                $response = [
                    "status" => false,
                    "message" => 'Папка с таким именем в данном месте уже существует!'
                ];

                echo json_encode($response);
                die();
            }

            $renameDir->execute();
            $renameDirAtFiles->execute();

            $this->connection->commit();

            $response = [
                "status" => true,
                "message" => 'Папка была успешно переименована'
            ];

            echo json_encode($response);
        } catch (\PDOException $exception) {
            $this->connection->rollBack();

            $response = [
                "status" => false,
                "message" => $exception->getMessage()
            ];

            echo json_encode($response);
        }
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

        // ищем path папки для дальнейших манипуляций
        $getDirPath = $this->connection->prepare("SELECT `path` FROM `directories` WHERE `dir_id` = :dirId AND `user_id` = :userId");
        $getDirPath->bindValue('dirId', $dirId);
        $getDirPath->bindValue('userId', $userId);

        $getDirPath->execute();
        $path = $getDirPath->fetchAll(\PDO::FETCH_ASSOC);
        $path = $path[0]['path']; // путь удаляемой папки

        // ищем все папки в удаляемой папке
        $getAllDirs = $this->connection->prepare("SELECT `dir_id` FROM `directories` WHERE `path` LIKE '$path%' AND `user_id` = :userId");
        $getAllDirs->bindValue('userId', $userId);

        $getAllDirs->execute();
        $result = $getAllDirs->fetchAll(\PDO::FETCH_ASSOC);

        $newArray = [];

        array_walk_recursive($result, function ($item) use (&$newArray) { // преобразовываем массив в одномерный для удобной работы с ним
            $newArray[] = $item;
        });

        // ищем id файлов, которые находятся в данных папках
        $searchFiles = $this->connection->prepare("SELECT `file_id` FROM `files` WHERE `dir_id` = :dirId AND `id` = :userId");
        $searchFiles->bindValue('userId', $userId);
        $searchFiles->bindParam('dirId', $idForDir);

        // ищем закодированные имена файлов
        $searchEncodedFileNames = $this->connection->prepare("SELECT `encoded_name` FROM `files` WHERE `file_id` = :fileId AND `id` = :userId AND `type` = 'file'");
        $searchEncodedFileNames->bindValue('userId', $userId);
        $searchEncodedFileNames->bindParam('fileId', $idForFile);

        // удаляем строки файлов из таблицы файлов
        $deleteFromFiles = $this->connection->prepare("DELETE FROM `files` WHERE `file_id` = :fileId AND `id` = :userId AND `type` = 'file'");
        $deleteFromFiles->bindValue('userId', $userId);
        $deleteFromFiles->bindParam('fileId', $idForFile);

        // удаляем строки папок из таблицы файлов
        $deleteDirsFromFiles = $this->connection->prepare("DELETE FROM `files` WHERE `belong_dir_id` = :dirId AND `id` = :userId AND `type` = 'dir'");
        $deleteDirsFromFiles->bindValue('userId', $userId);
        $deleteDirsFromFiles->bindParam('dirId', $idDir);

        // удаляем строки из таблицы directories
        $deleteDirs = $this->connection->prepare("DELETE FROM `directories` WHERE `dir_id` = :dirId AND `user_id` = :userId");
        $deleteDirs->bindValue('userId', $userId);
        $deleteDirs->bindParam('dirId',  $dirId);

        try {
            $this->connection->beginTransaction();

            $filesId = []; // id удаляемых файлов и папок в таблице files

            foreach ($newArray as $idForDir) {
                $searchFiles->execute();

                $filesId[] = $searchFiles->fetchAll(\PDO::FETCH_ASSOC);
            }

            foreach ($newArray as $idDir) {
                $deleteDirsFromFiles->execute();
            }

            $newFilesId = [];

            array_walk_recursive($filesId, function ($item) use (&$newFilesId) { // преобразовываем массив в одномерный для удобной работы с ним
                $newFilesId[] = $item;
            });

            $encodedFileNamesArray = []; // массив для закодированных имен файлов для последующего преобразования массива в удобный для работы вид

            foreach ($newFilesId as $idForFile) {
                $searchEncodedFileNames->execute();

                $encodedFileNamesArray[] = $searchEncodedFileNames->fetchAll(\PDO::FETCH_ASSOC);

                $deleteFromFiles->execute();
            }

            $newEncodedNamesArray = []; // массив с именами файлов, которые будут удалены из папки storage

            array_walk_recursive($encodedFileNamesArray, function ($item) use (&$newEncodedNamesArray) {
                $newEncodedNamesArray[] = $item;
            });

            foreach ($newEncodedNamesArray as $name) {
                $path = self::PATH_TO_STORAGE . $name;

                unlink($path); // удаление файлов из главного хранилища (./storage)
            }

            foreach ($newArray as $dirId) {
                $deleteDirs->execute();
            }

            $this->connection->commit();

            echo json_encode([
               "status" => true,
               "message" => 'Папка была успешно удалена'
            ]);
        } catch (\PDOException $exception) {
            $this->connection->rollBack();
            echo json_encode([
                "status" => false,
                "message" => $exception->getMessage()
            ]);
        }
    }
}