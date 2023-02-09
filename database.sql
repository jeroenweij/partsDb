-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 04, 2023 at 09:48 AM
-- Server version: 10.6.11-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `components`
--
CREATE DATABASE IF NOT EXISTS `components` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `components`;

-- --------------------------------------------------------

--
-- Table structure for table `companys`
--

CREATE TABLE `companys` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `address` varchar(200) NOT NULL,
  `logo` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `extstock`
--

CREATE TABLE `extstock` (
  `id` int(11) NOT NULL,
  `part` int(11) NOT NULL,
  `relation` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderpart`
--

CREATE TABLE `orderpart` (
  `orderId` int(11) NOT NULL,
  `part` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `packed` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderproject`
--

CREATE TABLE `orderproject` (
  `orderId` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `relation` int(11) NOT NULL,
  `company` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partproject`
--

CREATE TABLE `partproject` (
  `part` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

CREATE TABLE `parts` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `type` int(11) NOT NULL,
  `value` float DEFAULT NULL,
  `description` varchar(120) DEFAULT NULL,
  `package` int(11) NOT NULL,
  `unit` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `relations`
--

CREATE TABLE `relations` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `contact` varchar(80) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `partId` int(11) NOT NULL,
  `location` int(11) NOT NULL,
  `sublocation` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companys`
--
ALTER TABLE `companys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `extstock`
--
ALTER TABLE `extstock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `part` (`part`),
  ADD KEY `relation` (`relation`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orderpart`
--
ALTER TABLE `orderpart`
  ADD KEY `order` (`orderId`),
  ADD KEY `part` (`part`);

--
-- Indexes for table `orderproject`
--
ALTER TABLE `orderproject`
  ADD KEY `order` (`orderId`),
  ADD KEY `project` (`project`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `relation` (`relation`),
  ADD KEY `company` (`company`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partproject`
--
ALTER TABLE `partproject`
  ADD KEY `part` (`part`),
  ADD KEY `project` (`project`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `package` (`package`),
  ADD KEY `unit` (`unit`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `relations`
--
ALTER TABLE `relations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partId` (`partId`),
  ADD KEY `locationId` (`location`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag`),
  ADD KEY `part` (`part`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- INSERT data
--

INSERT INTO `packages` (`id`, `name`) VALUES (1, '-');
INSERT INTO `types` (`id`, `name`) VALUES (1, '-');
INSERT INTO `units` (`id`, `name`) VALUES (1, '-');

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companys`
--
ALTER TABLE `companys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `extstock`
--
ALTER TABLE `extstock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `relations`
--
ALTER TABLE `relations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `extstock`
--
ALTER TABLE `extstock`
  ADD CONSTRAINT `extstock_ibfk_1` FOREIGN KEY (`part`) REFERENCES `parts` (`id`),
  ADD CONSTRAINT `extstock_ibfk_2` FOREIGN KEY (`relation`) REFERENCES `relations` (`id`);

--
-- Constraints for table `orderpart`
--
ALTER TABLE `orderpart`
  ADD CONSTRAINT `orderpart_ibfk_1` FOREIGN KEY (`orderId`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `orderpart_ibfk_2` FOREIGN KEY (`part`) REFERENCES `parts` (`id`);

--
-- Constraints for table `orderproject`
--
ALTER TABLE `orderproject`
  ADD CONSTRAINT `orderproject_ibfk_1` FOREIGN KEY (`orderId`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `orderproject_ibfk_2` FOREIGN KEY (`project`) REFERENCES `projects` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`relation`) REFERENCES `relations` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`company`) REFERENCES `companys` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`status`) REFERENCES `statuses` (`id`);

--
-- Constraints for table `partproject`
--
ALTER TABLE `partproject`
  ADD CONSTRAINT `partproject_ibfk_1` FOREIGN KEY (`part`) REFERENCES `parts` (`id`),
  ADD CONSTRAINT `partproject_ibfk_2` FOREIGN KEY (`project`) REFERENCES `projects` (`id`);

--
-- Constraints for table `parts`
--
ALTER TABLE `parts`
  ADD CONSTRAINT `parts_ibfk_1` FOREIGN KEY (`type`) REFERENCES `types` (`id`),
  ADD CONSTRAINT `parts_ibfk_2` FOREIGN KEY (`package`) REFERENCES `packages` (`id`),
  ADD CONSTRAINT `parts_ibfk_3` FOREIGN KEY (`unit`) REFERENCES `units` (`id`);

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`partId`) REFERENCES `parts` (`id`),
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`location`) REFERENCES `locations` (`id`);

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`part`) REFERENCES `parts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
