<?php

namespace controllers;

class Files
{
    protected object $connection;

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

    // проверка на авторизованность
    protected function checkAuth(array $array) : bool
    {
        if (isset($array['authorized'])) {
            if (!$array['authorized']) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    // получение id пользователя
    protected function getUserId($sessionId, $userName)
    {
        $statement = $this->connection->prepare("SELECT `id` FROM `users_list` WHERE `session` = :session AND `login` = :login");
        $statement->bindValue('session', $sessionId);
        $statement->bindValue('login', $userName);

        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) === 0) {
                return [
                    "status" => false,
                    "message" => 'Необходимо авторизоваться'
                ];
            }

            return [
                "status" => true,
                "id" => $result[0]['id']
            ];
        }
    }

    // проверка имени файла/папки и зашифровка имени
    protected function checkFileOrDirName($name)
    {
        //конвертер символов кириллицы
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',    'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',    'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',    'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',    'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',  'ь' => '',    'ы' => 'y',   'ъ' => '',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',    'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',    'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',    'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',    'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',  'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );

        //регулярное выражение для проверки имени файла
        $pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";

        $checkedName = mb_eregi_replace($pattern, '-', $name);
        $checkedName = mb_ereg_replace('[-]+', '-', $checkedName);

        return strtr($checkedName, $converter);
    }

    // получение информации о конкретном файле
    public function getFileInfo(array $params)
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
        $fileId = $params[0];

        $statement = $this->connection->prepare("SELECT `encoded_name`, `real_name` FROM `files` WHERE `file_id` = :fileId AND `id` = :userId");
        $statement->bindValue('fileId', $fileId);
        $statement->bindValue('userId', $userId);

        $statement->execute();

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) === 0) {
            $response = [
                "status" => false,
                "message" => 'Файл не был найден'
            ];

            echo json_encode($response);
            die();
        }

        $link = self::PATH_TO_STORAGE . $result[0]['encoded_name'];

        $fileInfo = [
            "real_name" => $result[0]['real_name'],
            "link" => $link
        ];

        $response = [
            "status" => true,
            "file_info" => $fileInfo
        ];

        echo json_encode($response);
    }

    // добавление файла
    public function createFile()
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

        if (empty($_FILES)) {
            $response = [
                "status" => false,
                "message" => 'Файл не был загружен!'
            ];

            echo json_encode($response);
            die();
        }

        $dirPath = $_POST['path'];
        $file = $_FILES['file'];
        $name = $file['name'];
        $fileSize = $file['size'];

        $getDirId = $this->connection->prepare("SELECT `dir_id` FROM `directories` WHERE `path` = :path AND `user_id` = :userId");
        $getDirId->bindParam('path', $dirPath);
        $getDirId->bindValue('userId', $userId);

        $getDirId->execute();

        $result = $getDirId->fetchAll(\PDO::FETCH_ASSOC);

        $dirId = $result[0]['dir_id'];

        $statement = $this->connection->prepare("INSERT INTO `files`(`dir_id`, `type`,`encoded_name`, `real_name`, `id`) VALUES (:dirId, 'file', :encodedName, :realName, :userId)");
        $statement->bindParam('dirId', $dirId);
        $statement->bindParam('encodedName', $encodedFileName);
        $statement->bindParam('realName', $name);
        $statement->bindParam('userId', $userId);

        if ($fileSize > 2147483648) {
            $response = [
                "status" => false,
                "message" => 'Превышен допустимый размер загружаемого файла! (не более 2ГБ)'
            ];

            echo json_encode($response);
            die();
        }

        $encodedFileName = $this->checkFileOrDirName($name);
        $fileName = pathinfo($encodedFileName, PATHINFO_FILENAME); // получение имени файла
        $fileExtension = pathinfo($encodedFileName, PATHINFO_EXTENSION); // получение расширения файла

        $dirResult = scandir(self::PATH_TO_STORAGE);

        // проверка на существование файла в корневой папке с таким именем
        for ($i = 0; $i < count($dirResult); $i++) {
            if ($encodedFileName === $dirResult[$i]) {
                $encodedFileName = $fileName . '_' . --$i . '.' .$fileExtension;
            }
        }

        if (move_uploaded_file($file['tmp_name'], self::PATH_TO_STORAGE . $encodedFileName)) {
            if (!$statement->execute()) {
                $response = [
                    "status" => false,
                    "message" => 'Что-то пошло не так...'
                ];

                echo json_encode($response);
                die(http_response_code(500));
            }

            $response = [
                "status" => true,
                "message" => 'Файл был успешно загружен!'
            ];

            echo json_encode($response);
        }
    }

    // изменение файла
    public function updateFile(array $params)
    {
        session_start();

        if (!$this->checkAuth($_SESSION)) {
            echo json_encode([
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ]);
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
        $fileId = $params[0]; // id изменяемого файла
        $input = json_decode(file_get_contents('php://input'), true); // получаем данные с формы

        $method = $input['method'];

        if ($method === 'rename') {
            $newFileName = trim($input['newFileName']);
            $path = trim($input['path']);

            if ($newFileName === '') {
                $response = [
                    "status" => false,
                    "message" => 'Поле не должно быть пустым!'
                ];
                echo json_encode($response);
                die();
            }

            // получаем информацию об изменяемом файле
            $getFile = $this->connection->prepare("SELECT * FROM `files` WHERE `file_id` = :fileId AND `id` = :userId");
            $getFile->bindValue('userId', $userId);
            $getFile->bindValue('fileId', $fileId);

            // ищем папку, в которой находится данный файл
            $getDir = $this->connection->prepare("SELECT `dir_id` FROM `directories` WHERE `path` = :path AND `user_id` = :userId");
            $getDir->bindValue('path', $path);
            $getDir->bindValue('userId', $userId);

            // проверка на существование файла с таким именем в данной папке
            $getDirFiles = $this->connection->prepare("SELECT `real_name` FROM `files` WHERE `dir_id` = :dirId AND `id` = :userId AND `real_name` = :newRealName");
            $getDirFiles->bindValue('userId', $userId);
            $getDirFiles->bindValue('newRealName', $newFileName);
            $getDirFiles->bindParam('dirId', $dirId);

            $updateFile = $this->connection->prepare("UPDATE `files` SET `real_name` = :newRealName WHERE `id` = :userId AND `file_id` = :fileId");
            $updateFile->bindValue('userId', $userId);
            $updateFile->bindValue('newRealName', $newFileName);
            $updateFile->bindValue('fileId', $fileId);

            try {
                $this->connection->beginTransaction();

                $getFile->execute();

                $fileInfo = $getFile->fetch(\PDO::FETCH_ASSOC);

                $oldRealName = $fileInfo['real_name'];

                if ($oldRealName === $newFileName) {
                    $response = [
                        "status" => false,
                        "message" => 'Новое имя файла не должно совпадать со старым!'
                    ];
                    echo json_encode($response);
                    die();
                }

                $getDir->execute();

                $dir = $getDir->fetch(\PDO::FETCH_ASSOC);
                $dirId = $dir['dir_id'];

                $getDirFiles->execute();
                $allFiles = $getDirFiles->fetchAll(\PDO::FETCH_ASSOC);

                if (count($allFiles) > 0) {
                    $response = [
                        "status" => false,
                        "message" => 'Файл с таким именем уже существует в данной папке!'
                    ];
                    echo json_encode($response);
                    die();
                }

                $updateFile->execute();

                $this->connection->commit();

                $response = [
                    "status" => true,
                    "message" => 'Файл был успешно переименован'
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

            exit;
        }

        if ($method === 'move') {
            if (!isset($input['newPath'])) {
                $response = [
                    "status" => false,
                    "message" => 'Необходимо выбрать один из вариантов!'
                ];
                echo json_encode($response);
                die();
            }

            $newPath = $input['newPath'];

            // получение данных о файле
            $getFile = $this->connection->prepare("SELECT * FROM `files` WHERE `file_id` = :fileId AND `id` = :userId");
            $getFile->bindValue('userId', $userId);
            $getFile->bindValue('fileId', $fileId);

            // проверка на существование выбранной папки
            $getNewDir = $this->connection->prepare("SELECT * FROM `directories` WHERE `path` = :newPath AND `user_id` = :userId");
            $getNewDir->bindValue('newPath', $newPath);
            $getNewDir->bindValue('userId', $userId);

            // проверка существования файла с таким именем в выбранной папке
            $checkThisDir = $this->connection->prepare("SELECT * FROM `files` WHERE `dir_id` = :dirId AND `real_name` = :realName AND `id` = :userId");
            $checkThisDir->bindValue('userId', $userId);
            $checkThisDir->bindParam('dirId', $id);
            $checkThisDir->bindParam('realName', $fileRealName);

            // перемещение файла в выбранную папку
            $moveFile = $this->connection->prepare("UPDATE `files` SET `dir_id` = :dirId WHERE `file_id` = :fileId AND `id` = :userId");
            $moveFile->bindValue('userId', $userId);
            $moveFile->bindParam('dirId', $id);
            $moveFile->bindValue('fileId', $fileId);

            try {
                $this->connection->beginTransaction();

                $getFile->execute();
                $file = $getFile->fetch(\PDO::FETCH_ASSOC);
                $fileRealName = $file['real_name'];

                $getNewDir->execute();
                $dir = $getNewDir->fetch(\PDO::FETCH_ASSOC);

                if (empty($dir)) {
                    $response = [
                        "status" => false,
                        "message" => 'Такой папки не существует!'
                    ];
                    echo json_encode($response);
                    die();
                }

                $id = $dir['dir_id']; // id папки, в которую будет перемещен файл

                $checkThisDir->execute();
                $result = $checkThisDir->fetchAll();

                if (count($result) > 0) {
                    $response = [
                        "status" => false,
                        "message" => 'Такой файл уже есть в выбранной папке!'
                    ];
                    echo json_encode($response);
                    die();
                }

                $moveFile->execute();

                $this->connection->commit();

                $response = [
                    "status" => true,
                    "message" => 'Файл был успешно перемещен'
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

            exit;
        }
    }

    // удаление файла
    public function deleteFile(array $params)
    {
        session_start();

        if (!$this->checkAuth($_SESSION)) {
            json_encode([
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ]);
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
        $fileId = $params[0]; // id удаляемого файла

        // получаем закодированное имя файла для удаления в главном хранилище (./storage)
        $getEncodedFileName = $this->connection->prepare("SELECT `encoded_name` FROM `files` WHERE `id` = :userId AND `file_id` = :fileId");
        $getEncodedFileName->bindValue('userId', $userId);
        $getEncodedFileName->bindValue('fileId', $fileId);

        // удаление файла из БД
        $deleteFileFromTable = $this->connection->prepare("DELETE FROM `files` WHERE `file_id` = :fileId AND `id` = :userId");
        $deleteFileFromTable->bindValue('userId', $userId);
        $deleteFileFromTable->bindValue('fileId', $fileId);

        // удаление разрешения на доступ к файлу другим пользователям
        $deleteShares = $this->connection->prepare("DELETE FROM `shared_files` WHERE `file_id` = :fileId");
        $deleteShares->bindValue('fileId', $fileId);
        $deleteShares->execute();

        $getEncodedFileName->execute();
        $fileName = $getEncodedFileName->fetch(\PDO::FETCH_ASSOC);
        $fileName = $fileName['encoded_name'];

        // удаляем файл из папки
        if (unlink(self::PATH_TO_STORAGE . $fileName)) {
            $deleteFileFromTable->execute();

            echo json_encode([
                "status" => true,
                "message" => 'Файл был успешно удален'
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => 'Не удалось удалить данный файл'
            ]);
            die(http_response_code(500));
        }
    }

    // разрешение доступа к файлу
    public function giveAccessToFile()
    {
        session_start();

        if (!$this->checkAuth($_SESSION)) {
            echo json_encode([
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ]);
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

        $fileId = $input['fileId']; // получаем id файла, к которому необходимо предоставить доступ
        $userId = $input['userId']; // получаем id пользователя, которому нужно предоставить доступ к файлу

        // проверка на существование такой записи в БД
        $checkAccess = $this->connection->prepare("SELECT * FROM `shared_files` WHERE `file_id` = :fileId AND `user_id` = :userId");
        $checkAccess->bindValue('fileId', $fileId);
        $checkAccess->bindValue('userId', $userId);
        $checkAccess->execute();

        $list = $checkAccess->fetchAll();

        if (count($list) > 0) {
            $response = [
                "status" => false,
                "message" => 'Доступ данному пользователю уже предоставлен!'
            ];
            echo json_encode($response);
            die();
        }

        // запись в БД
        $postAccess = $this->connection->prepare("INSERT INTO `shared_files`(`file_id`, `user_id`) VALUES (:fileId, :userId)");
        $postAccess->bindValue('fileId', $fileId);
        $postAccess->bindValue('userId', $userId);

        if ($postAccess->execute()) {
            $response = [
                "status" => true,
                "message" => 'Доступ был разрешен'
            ];
            echo json_encode($response);
        } else {
            $response = [
                "status" => false,
                "message" => 'Что-то пошло не так...'
            ];
            echo json_encode($response);
            die();
        }
    }

    // получение списка пользователей, у которых есть доступ к данному файлу
    public function getSharedUsersList(array $params)
    {
        session_start();

        if (!$this->checkAuth($_SESSION)) {
            echo json_encode([
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ]);
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


        $fileId = $params[0]; // id файла
        $userId = $user['id']; // id владельца файла

        // получаем список пользователей, у которых есть доступ к файлу
        $getUsersList = $this->connection->prepare("SELECT `users_list`.`id`, `users_list`.`login` FROM `users_list`, `shared_files` WHERE `shared_files`.`file_id` = :fileId AND `users_list`.`id` <> :userId AND `shared_files`.`user_id` = `users_list`.`id`");
        $getUsersList->bindValue('userId', $userId);
        $getUsersList->bindValue('fileId', $fileId);
        $getUsersList->execute();

        $result = $getUsersList->fetchAll(\PDO::FETCH_ASSOC);

        $response = [
            "status" => false,
            "users" => $result
        ];

        echo json_encode($response);
    }

    // удаление разрешения на доступ к файлу
    public function denyAccessForUser()
    {
        session_start();

        if (!$this->checkAuth($_SESSION)) {
            echo json_encode([
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ]);
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

        $fileId = $input['fileId'];
        $userId = $input['userId'];

        // проверка на существование доступа к файлу
        $checkAccess = $this->connection->prepare("SELECT * FROM `shared_files` WHERE `file_id` = :fileId AND `user_id` = :userId");
        $checkAccess->bindValue('fileId', $fileId);
        $checkAccess->bindValue('userId', $userId);

        // отзыв доступа к файлу
        $denyAccess = $this->connection->prepare("DELETE FROM `shared_files` WHERE `file_id` = :fileId AND `user_id` = :userId");
        $denyAccess->bindValue('fileId', $fileId);
        $denyAccess->bindValue('userId', $userId);

        try {
            $this->connection->beginTransaction();

            $checkAccess->execute();

            $list = $checkAccess->fetchAll();

            if (count($list) == 0) {
                $response = [
                    "status" => false,
                    "message" => 'У пользователя нет прав доступа!'
                ];
                echo json_encode($response);
                die();
            }

            $denyAccess->execute();

            $this->connection->commit();

            $response = [
                "status" => true,
                "message" => 'Разрешение доступа данного пользователя было отозвано'
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

    // список доступных пользователю файлов
    public function getSharedFilesList() {
        session_start();

        if (!$this->checkAuth($_SESSION)) {
            json_encode([
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ]);
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

        // получение списка файлов, к которым есть доступ у пользователя
        $getFilesList = $this->connection->prepare("SELECT `files`.`real_name`, `files`.`encoded_name` FROM `files`, `shared_files` WHERE `shared_files`.`user_id` = :userId AND `files`.`file_id` = `shared_files`.`file_id`");
        $getFilesList->bindValue('userId', $userId);
        $getFilesList->execute();

        $result = $getFilesList->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) == 0) {
            $response = [
                "status" => 'empty',
                "message" => 'Вам пока не дали доступ к файлам...'
            ];

            echo json_encode($response);
            die();
        }

        $newArray = [];

        foreach ($result as $file) {
            $link = self::PATH_TO_STORAGE . $file['encoded_name'];

            $newArray[] = [
                "real_name" => $file['real_name'],
                "link" => $link
            ];
        }

        $response = [
            "status" => true,
            "list" => $newArray
        ];

        echo json_encode($response);
    }
}