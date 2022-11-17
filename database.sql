SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `components` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `components`;

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `locations` (`id`, `name`) VALUES
(1, '-');

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `packages` (`id`, `name`) VALUES
(1, '-');

CREATE TABLE `partproject` (
  `part` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `parts` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `type` int(11) NOT NULL,
  `value` float DEFAULT NULL,
  `description` varchar(120) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `package` int(11) NOT NULL,
  `unit` int(11) NOT NULL,
  `location` int(11) NOT NULL DEFAULT 1,
  `sublocation` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tags` (
  `tag` varchar(32) NOT NULL,
  `part` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `types` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `types` (`id`, `name`) VALUES
(1, '-');

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `units` (`id`, `name`) VALUES
(1, '-');


ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `partproject`
  ADD KEY `part` (`part`),
  ADD KEY `project` (`project`);

ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `package` (`package`),
  ADD KEY `unit` (`unit`),
  ADD KEY `location` (`location`);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag`),
  ADD KEY `part` (`part`);

ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `partproject`
  ADD CONSTRAINT `partproject_ibfk_1` FOREIGN KEY (`part`) REFERENCES `parts` (`id`),
  ADD CONSTRAINT `partproject_ibfk_2` FOREIGN KEY (`project`) REFERENCES `projects` (`id`);

ALTER TABLE `parts`
  ADD CONSTRAINT `parts_ibfk_1` FOREIGN KEY (`type`) REFERENCES `types` (`id`),
  ADD CONSTRAINT `parts_ibfk_2` FOREIGN KEY (`package`) REFERENCES `packages` (`id`),
  ADD CONSTRAINT `parts_ibfk_3` FOREIGN KEY (`unit`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `parts_ibfk_4` FOREIGN KEY (`location`) REFERENCES `locations` (`id`);

ALTER TABLE `tags`
  ADD CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`part`) REFERENCES `parts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
