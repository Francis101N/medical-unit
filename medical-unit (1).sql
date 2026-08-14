-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 14, 2026 at 05:53 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medical-unit`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(10) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL,
  `medical_head` varchar(100) NOT NULL,
  `medical_head_email` varchar(150) NOT NULL,
  `medical_head_passport` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `state`, `medical_head`, `medical_head_email`, `medical_head_passport`, `type`, `date_created`) VALUES
(1, 'H-Capital', 'Abuja', 'Mr Adamu', 'mradamu123@gmail.com', '', 'onshore', '2026-05-07 10:34:25'),
(2, 'Fingesi Lekki Phase1', 'Lagos', 'Mrs tokunbo', 'Mrstokunbo1972@gmail.com', '', 'onshore', '2026-05-07 10:34:25'),
(3, 'Omisore Lekki Phase1', 'Lagos', 'Mr Omisore', 'MrOmisore1982@gmail.com', '', 'onshore', '2026-05-07 10:37:15'),
(4, 'Wole olajeju Lekki Phase1', 'Lagos', 'Miss Wole olajeju', 'MissWoleolajeju01@gmail.com', '', 'onshore', '2026-05-07 10:37:15'),
(5, 'Apapa', 'Lagos', 'Doctor Apapa', 'doctorapapa23@gmail.com', '', 'onshore', '2026-05-07 11:01:33'),
(6, 'Ijora', 'Lagos', 'Pharmacy Rita osefo', 'pharmacyritaosefo2@gmail.com', '', 'onshore', '2026-05-07 11:01:33'),
(7, 'Ph', 'Port Harcourt', 'Barister matthew', 'baristermatthew00@gmail.com', '', 'onshore', '2026-05-07 11:09:11'),
(8, 'Eket', 'Akwa Ibom', 'Miss Christainia princess', 'misschristainiaprincess@gmail.com', '', 'onshore', '2026-05-07 11:09:11'),
(9, 'umudike', 'Abia State', 'Mr jerry Equal', 'Mrjerryequal0982@gmail.com', '', 'onshore', '2026-05-07 12:53:44'),
(10, 'St.peters', 'Akwa ibom', 'Daniel chekwu', 'danielchekwu1972@gmail.com', 'passport_6a7b58a75c23b4.57715084.jpg', 'offshore', '2026-05-07 12:57:20'),
(11, 'Zion Grace', 'Akwa Ibom', 'Francis Nwankwo', 'francisnwankwo1972@gmail.com', 'passport_69fe32e4b6cd53.09356620.webp', 'offshore', '2026-05-07 12:57:20'),
(12, 'Zion Glory', 'Akwa ibom', 'Grace Amadi', 'graceamadi231@gmail.com', 'passport_6a7ad3beb9f620.50073666.png', 'offshore', '2026-05-07 12:58:53');

-- --------------------------------------------------------

--
-- Table structure for table `drugs_allocations`
--

CREATE TABLE `drugs_allocations` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `allocated_qty` int(11) NOT NULL DEFAULT 0,
  `current_balance` int(11) NOT NULL DEFAULT 0,
  `last_allocated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drugs_allocations`
--

INSERT INTO `drugs_allocations` (`id`, `branch_id`, `drug_id`, `allocated_qty`, `current_balance`, `last_allocated_at`, `date_created`) VALUES
(7, 3, 2, 4, 2, '2026-08-14 14:29:29', '2026-08-13 14:46:18'),
(8, 2, 2, 1, 0, '2026-08-13 15:36:53', '2026-08-13 14:48:57'),
(9, 1, 2, 2, 2, '2026-08-14 07:44:02', '2026-08-14 08:44:02');

-- --------------------------------------------------------

--
-- Table structure for table `drugs_master`
--

CREATE TABLE `drugs_master` (
  `id` int(11) NOT NULL,
  `drug_code` varchar(50) NOT NULL,
  `drug_name` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `strength` varchar(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `dosage_form` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drugs_master`
--

INSERT INTO `drugs_master` (`id`, `drug_code`, `drug_name`, `generic_name`, `category`, `strength`, `quantity`, `dosage_form`, `created_at`) VALUES
(2, 'DRG-0001', 'Panadol', 'Paracetamol (Acetaminophen)', 'Analgesic / Antipyreticc', '500mg', 3, 'Tablet', '2026-08-13 12:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `drugs_stock_logs`
--

CREATE TABLE `drugs_stock_logs` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `processed_by` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drugs_stock_logs`
--

INSERT INTO `drugs_stock_logs` (`id`, `branch_id`, `drug_id`, `transaction_type`, `quantity`, `processed_by`, `notes`, `created_at`) VALUES
(3, 3, 2, 'allocation', 3, 'Mrs Grace Equal', 'Dispatched 3 units from Master Warehouse to Omisore Lekki Phase1.', '2026-08-13 13:46:18'),
(4, 2, 2, 'allocation', 1, 'Mrs Grace Equal', 'Dispatched 1 units from Master Warehouse to Fingesi Lekki Phase1.', '2026-08-13 13:48:57'),
(5, 3, 2, 'allocation', 1, 'Mrs Grace Equal', 'Dispatched 1 units from Master Warehouse to Omisore Lekki Phase1.', '2026-08-13 14:43:41'),
(6, 3, 2, 'dispense', 1, '1', 'Dispensed 1 unit of Panadol 500mg (Tablet) to staff member Ebube Nwankwo (EQ/LAG/AE/101/2026).', '2026-08-13 15:35:39'),
(7, 2, 2, 'dispense', 1, '1', 'Dispensed 1 unit of Panadol 500mg (Tablet) to staff member Francis Nwankwoo (EQ/LAG/AD/301/2026).', '2026-08-13 15:36:53'),
(8, 1, 2, 'allocation', 2, 'Mrs Grace Equal', 'Dispatched 2 units from Master Warehouse to H-Capital.', '2026-08-14 07:44:02'),
(9, 3, 2, 'dispense', 1, '1', 'Dispensed 1 unit of Panadol 500mg (Tablet) to staff member Ebube Nwankwo (UP/LAG/AE/101/2026).', '2026-08-14 14:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(50) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `passport` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` enum('active','inactive','suspended','terminated') DEFAULT 'active',
  `address` text DEFAULT NULL,
  `next_of_kin` varchar(150) DEFAULT NULL,
  `next_of_kin_phone` varchar(20) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `genotype` varchar(10) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `last_medical_checkup` date DEFAULT NULL,
  `fitness_status` enum('fit','unfit','under_observation') DEFAULT 'fit',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`id`, `staff_id`, `fullname`, `email`, `phone`, `gender`, `dob`, `passport`, `branch_id`, `company`, `department`, `role`, `employment_type`, `hire_date`, `status`, `address`, `next_of_kin`, `next_of_kin_phone`, `blood_group`, `genotype`, `allergies`, `medical_conditions`, `emergency_contact_name`, `emergency_contact_phone`, `last_medical_checkup`, `fitness_status`, `created_at`, `updated_at`) VALUES
(3, 'EQ/LAG/AD/301/2026', 'Francis Nwankwoo', 'francisnwankwo1972@gmail.com', '07010010811', 'male', '2026-08-17', '1786444663_8765.png', 2, 'Equal Logistics', 'information_technology', 'Developer', 'part_time', '2026-08-11', 'inactive', 'No 30 EMMA Avenue', 'bs', '08069815240', 'O+', 'AA', 'High Scent/smell', 'cold', 'Ebube Nwankwo', '08069815240', '2026-08-11', 'fit', '2026-08-11 10:37:43', '2026-08-14 13:31:08'),
(4, 'UP/LAG/AE/101/2026', 'Ebube Nwankwo', 'francisnwankwo37@gmail.com', '08069815240', 'female', '2026-08-11', '1786527268_8195.png', 3, 'Upstream DC', 'human_resources', 'Chief Executive Officer', 'full_time', '2026-08-04', 'active', 'No50 ifite awka', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', '3881595498', '2026-08-12', 'fit', '2026-08-12 09:34:28', '2026-08-14 13:56:44');

-- --------------------------------------------------------

--
-- Table structure for table `staff_medical_records`
--

CREATE TABLE `staff_medical_records` (
  `id` int(11) NOT NULL,
  `staff_name` varchar(50) NOT NULL,
  `company` varchar(100) NOT NULL,
  `staff_branch` varchar(100) DEFAULT NULL,
  `intake_time` datetime NOT NULL,
  `release_time` datetime DEFAULT NULL,
  `diagnosis` text NOT NULL,
  `symptoms` text DEFAULT NULL,
  `medical_notes` text DEFAULT NULL,
  `treatment_given` text DEFAULT NULL,
  `drugs_given` text DEFAULT NULL,
  `dosage_instructions` text DEFAULT NULL,
  `attended_by` varchar(150) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `condition_on_admission` enum('stable','critical','serious','minor') DEFAULT 'stable',
  `condition_on_release` enum('improved','stable','referred','deceased') DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `temperature` varchar(20) DEFAULT NULL,
  `pulse_rate` varchar(20) DEFAULT NULL,
  `follow_up_required` enum('yes','no') DEFAULT 'no',
  `follow_up_date` date DEFAULT NULL,
  `record_status` enum('open','closed','under_treatment') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_medical_records`
--

INSERT INTO `staff_medical_records` (`id`, `staff_name`, `company`, `staff_branch`, `intake_time`, `release_time`, `diagnosis`, `symptoms`, `medical_notes`, `treatment_given`, `drugs_given`, `dosage_instructions`, `attended_by`, `department`, `condition_on_admission`, `condition_on_release`, `blood_pressure`, `temperature`, `pulse_rate`, `follow_up_required`, `follow_up_date`, `record_status`, `created_at`, `updated_at`) VALUES
(12, 'Francis Nwankwoo', '', 'Fingesi Lekki phase1', '2026-08-02 12:22:00', '2026-08-04 13:30:00', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'information_technology', 'stable', 'improved', '20', '10', '50', 'no', '2026-08-05', 'under_treatment', '2026-08-12 11:23:21', '2026-08-12 12:30:24'),
(13, 'Ebube Nwankwo', '', 'Omisore Lekki Phase1', '2026-08-09 12:23:00', '2026-08-03 13:22:00', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'human_resources', 'critical', 'improved', '10', '20', '15', 'no', '2026-08-11', 'open', '2026-08-12 11:24:07', '2026-08-12 12:22:59'),
(14, 'Ebube Nwankwo', '', 'Omisore Lekki Phase1', '2026-08-13 09:07:00', NULL, '', 'HJNJN', '', 'MMM', 'MM', 'KKK', 'STELLA', 'human_resources', 'stable', NULL, '', '', '', 'no', NULL, 'open', '2026-08-13 08:08:38', '2026-08-13 08:08:38'),
(15, 'Ebube Nwankwo', '', 'Omisore Lekki Phase1', '2026-08-13 16:35:00', NULL, 'testing', 'testing', 'testing', 'testing', 'Panadol 500mg (Tablet)', 'testing', 'Mrs Grace Equall', 'human_resources', 'stable', 'improved', 'testing', 'testing', 'testing', 'no', '2026-08-13', 'open', '2026-08-13 15:35:39', '2026-08-14 08:08:43'),
(16, 'Francis Nwankwoo', 'Equal Logistics', 'Fingesi Lekki Phase1', '2026-08-13 16:36:00', NULL, 'testing', 'testing', 'testing', 'testing', 'Panadol 500mg (Tablet)', 'testing', 'Mrs Grace Equal', 'information_technology', 'stable', 'improved', '88', '788', '50', 'no', '2026-08-13', 'closed', '2026-08-13 15:36:53', '2026-08-14 15:19:22'),
(17, 'Ebube Nwankwo', 'Upstream DC', 'Omisore Lekki Phase1', '2026-08-15 15:28:00', NULL, 'mm', 'mmam', 'mm', 'mm', 'Panadol 500mg (Tablet)', 'mm', 'Mrs Grace Equalllllll', 'human_resources', 'stable', 'stable', '10', '10', '10', 'no', '2026-08-21', 'open', '2026-08-14 14:29:29', '2026-08-14 14:33:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `fullname` varchar(200) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `email`, `role`, `branch`, `date_created`) VALUES
(1, 'Mrs Grace Equal', 'Grace50', '$2y$10$bgvf7iG/yyVzbLevT5d.uePm5UpwyceP6UJnn1e1Nk5f2qLsLcU4a', 'chiefmedic@gmail.com', 'super-admin', 'Omisore Lekki Phase1', '2026-05-06 13:55:23'),
(2, 'Mr Alfred Donold', 'alfredD', '$2y$10$bgvf7iG/yyVzbLevT5d.uePm5UpwyceP6UJnn1e1Nk5f2qLsLcU4a', 'alfreddonald@gmail.com', 'staff', 'Fingesi Lekki Phase1', '2026-08-12 10:01:26'),
(3, 'Kofi Amuzu', 'kofi20', '$2y$10$bgvf7iG/yyVzbLevT5d.uePm5UpwyceP6UJnn1e1Nk5f2qLsLcU4a', 'kofiequal@gmail.com', 'staff', 'Omisore Lekki Phase1', '2026-08-12 13:12:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drugs_allocations`
--
ALTER TABLE `drugs_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_drug_unique` (`branch_id`,`drug_id`),
  ADD KEY `drug_id` (`drug_id`);

--
-- Indexes for table `drugs_master`
--
ALTER TABLE `drugs_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `drug_code` (`drug_code`);

--
-- Indexes for table `drugs_stock_logs`
--
ALTER TABLE `drugs_stock_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `staff_medical_records`
--
ALTER TABLE `staff_medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `drugs_allocations`
--
ALTER TABLE `drugs_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `drugs_master`
--
ALTER TABLE `drugs_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `drugs_stock_logs`
--
ALTER TABLE `drugs_stock_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_medical_records`
--
ALTER TABLE `staff_medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `drugs_allocations`
--
ALTER TABLE `drugs_allocations`
  ADD CONSTRAINT `drugs_allocations_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `drugs_allocations_ibfk_2` FOREIGN KEY (`drug_id`) REFERENCES `drugs_master` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
