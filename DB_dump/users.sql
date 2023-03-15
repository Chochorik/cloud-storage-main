-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 15 2023 г., 12:35
-- Версия сервера: 8.0.31
-- Версия PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `users`
--

-- --------------------------------------------------------

--
-- Структура таблицы `directories`
--

DROP TABLE IF EXISTS `directories`;
CREATE TABLE IF NOT EXISTS `directories` (
  `dir_id` int NOT NULL AUTO_INCREMENT,
  `path` varchar(1000) NOT NULL,
  `name` varchar(100) NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`dir_id`),
  KEY `10` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `directories`
--

INSERT INTO `directories` (`dir_id`, `path`, `name`, `user_id`) VALUES
(1, '/', 'root', 69),
(39, '/Файлы/', 'Файлы', 69),
(44, '/', 'root', 72),
(45, '/Файлы/Новая папка 2/', 'Новая папка 2', 69);

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE IF NOT EXISTS `files` (
  `file_id` int NOT NULL AUTO_INCREMENT,
  `dir_id` int NOT NULL,
  `type` varchar(10) NOT NULL,
  `encoded_name` varchar(100) NOT NULL,
  `real_name` varchar(100) NOT NULL,
  `belong_dir_id` int DEFAULT NULL,
  `id` int NOT NULL,
  PRIMARY KEY (`file_id`),
  KEY `id` (`id`),
  KEY `dir_id` (`dir_id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `files`
--

INSERT INTO `files` (`file_id`, `dir_id`, `type`, `encoded_name`, `real_name`, `belong_dir_id`, `id`) VALUES
(72, 1, 'file', 'Rectangle-6.jpg', 'Rectangle 6.jpg', NULL, 69),
(74, 1, 'dir', 'Fayly', 'Файлы', 39, 69),
(84, 1, 'file', 'Vector.svg', 'Vector.svg', NULL, 69),
(85, 39, 'file', '3.7-load.mp4', '3.7-load.mp4', NULL, 69),
(86, 39, 'dir', 'Novaya-papka-2', 'Новая папка 2', 45, 69);

-- --------------------------------------------------------

--
-- Структура таблицы `shared_files`
--

DROP TABLE IF EXISTS `shared_files`;
CREATE TABLE IF NOT EXISTS `shared_files` (
  `file_id` int NOT NULL,
  `user_id` int NOT NULL,
  KEY `file_id` (`file_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `users_list`
--

DROP TABLE IF EXISTS `users_list`;
CREATE TABLE IF NOT EXISTS `users_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pass_hash` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `salt` varchar(100) NOT NULL,
  `session` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `users_list`
--

INSERT INTO `users_list` (`id`, `login`, `email`, `pass_hash`, `salt`, `session`, `role`) VALUES
(69, 'Admin', 'dr.nikitamedvedev2018@gmail.com', '$2y$10$srtVI/RzUX3pUSUJlEvT3eK58WrmTsudi5xH4D8s2l9YNOuCoWI1y', 'f6925da0281e4de9a468789aa7bfaa55', 'cqntp6pa560hjiu9pumnbdguhp', 'admin'),
(72, 'test', '123@test.com', '$2y$10$L5.lut6Ug0TfbO7qyTYOn.HWZXY0q4mpXv93xMTGuY.S9DdFhSYr2', '12390e65b2efbb08612dbeb26a321e3d', NULL, 'user');

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `directories`
--
ALTER TABLE `directories`
  ADD CONSTRAINT `directories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_list` (`id`);

--
-- Ограничения внешнего ключа таблицы `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users_list` (`id`),
  ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`dir_id`) REFERENCES `directories` (`dir_id`);

--
-- Ограничения внешнего ключа таблицы `shared_files`
--
ALTER TABLE `shared_files`
  ADD CONSTRAINT `shared_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_list` (`id`),
  ADD CONSTRAINT `shared_files_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
