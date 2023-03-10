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
}