--
-- Database: `components`
--

-- --------------------------------------------------------

--
-- Table structure for table `companys`
--

CREATE TABLE `companys` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `address` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `extstock`
--

CREATE TABLE `extstock` (
  `id` int(11) NOT NULL,
  `part` int(11) NOT NULL,
  `relation` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orderpart`
--

CREATE TABLE `orderpart` (
  `orderId` int(11) NOT NULL,
  `part` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `packed` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orderproject`
--

CREATE TABLE `orderproject` (
  `orderId` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--
-- Table structure for table `relations`
--

CREATE TABLE `relations` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `contact` varchar(80) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
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
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
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






