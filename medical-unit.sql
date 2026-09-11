-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 11, 2026 at 11:36 AM
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
-- Table structure for table `branch_allocations`
--

CREATE TABLE `branch_allocations` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `branch_name` varchar(150) NOT NULL,
  `allocated_quantity` int(11) NOT NULL,
  `allocated_by` varchar(150) DEFAULT NULL,
  `allocation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_allocations`
--

INSERT INTO `branch_allocations` (`id`, `item_id`, `branch_name`, `allocated_quantity`, `allocated_by`, `allocation_date`) VALUES
(1, 2, 'umudike, Abia State', 2, 'Grace50', '2026-08-28 15:35:36'),
(2, 4, 'umudike, Abia State', 1, 'Grace50', '2026-08-28 15:38:34'),
(3, 3, 'umudike, Abia State', 3, 'Grace50', '2026-08-28 15:39:02'),
(4, 1, 'umudike, Abia State', 4, 'Grace50', '2026-08-28 15:41:05'),
(7, 3, 'umudike, Abia State', 1, 'Grace50', '2026-08-31 11:06:41'),
(8, 3, 'umudike, Abia State', 1, 'Grace50', '2026-08-31 12:04:45'),
(9, 2, 'umudike, Abia State', 3, 'Grace50', '2026-08-31 12:05:23'),
(10, 1, 'Apapa', 1, 'Grace50', '2026-08-31 12:11:45');

-- --------------------------------------------------------

--
-- Table structure for table `branch_stock_balance`
--

CREATE TABLE `branch_stock_balance` (
  `id` int(11) NOT NULL,
  `branch_name` varchar(150) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_remaining` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_stock_balance`
--

INSERT INTO `branch_stock_balance` (`id`, `branch_name`, `item_id`, `quantity_remaining`, `updated_at`) VALUES
(1, 'umudike, Abia State', 3, 2, '2026-08-31 12:04:45'),
(2, 'umudike, Abia State', 2, 3, '2026-08-31 12:05:23'),
(3, 'Apapa', 1, 1, '2026-08-31 12:11:45');

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
(9, 1, 2, 2, 2, '2026-08-14 07:44:02', '2026-08-14 08:44:02'),
(10, 2, 3, 3, 2, '2026-08-20 08:19:04', '2026-08-20 09:16:37'),
(11, 2, 5, 2, 1, '2026-08-20 08:19:04', '2026-08-20 09:17:13'),
(12, 2, 4, 3, 2, '2026-08-20 08:19:04', '2026-08-20 09:17:22');

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
(2, 'DRG-0001', 'Panadol', 'Paracetamol (Acetaminophen)', 'Analgesic / Antipyreticc', '500mg', 3, 'Tablet', '2026-08-13 12:25:24'),
(3, 'DRG-0281', 'Amoxil', 'Amoxicillin', 'Antibiotic', '300 mg', 7, 'Capsule', '2026-08-20 07:56:00'),
(4, 'DRG-1012', 'Ventolin', 'Salbutamol', 'Bronchodilator', '100 mcg/dose', 2, 'Inhaler', '2026-08-20 07:57:29'),
(5, 'DRG-1032', 'Insulin Actrapid', 'Human Insulin', 'Antidiabetic', '100 IU/mL', 0, 'Injection', '2026-08-20 07:59:15');

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
(9, 3, 2, 'dispense', 1, '1', 'Dispensed 1 unit of Panadol 500mg (Tablet) to staff member Ebube Nwankwo (UP/LAG/AE/101/2026).', '2026-08-14 14:29:29'),
(10, 2, 3, 'allocation', 3, 'Mrs Grace Equal', 'Dispatched 3 units from Master Warehouse to Fingesi Lekki Phase1.', '2026-08-20 08:16:37'),
(11, 2, 5, 'allocation', 2, 'Mrs Grace Equal', 'Dispatched 2 units from Master Warehouse to Fingesi Lekki Phase1.', '2026-08-20 08:17:13'),
(12, 2, 4, 'allocation', 3, 'Mrs Grace Equal', 'Dispatched 3 units from Master Warehouse to Fingesi Lekki Phase1.', '2026-08-20 08:17:22'),
(13, 2, 3, 'dispense', 1, '1', 'Dispensed 1 unit of Amoxil 300 mg (Capsule) to staff member Francis Nwankwoo (EQ/LAG/AD/301/2026).', '2026-08-20 08:19:04'),
(14, 2, 5, 'dispense', 1, '1', 'Dispensed 1 unit of Insulin Actrapid 100 IU/mL (Injection) to staff member Francis Nwankwoo (EQ/LAG/AD/301/2026).', '2026-08-20 08:19:04'),
(15, 2, 4, 'dispense', 1, '1', 'Dispensed 1 unit of Ventolin 100 mcg/dose (Inhaler) to staff member Francis Nwankwoo (EQ/LAG/AD/301/2026).', '2026-08-20 08:19:04');

-- --------------------------------------------------------

--
-- Table structure for table `outreach`
--

CREATE TABLE `outreach` (
  `id` int(1) NOT NULL,
  `project_title` varchar(225) NOT NULL,
  `location` varchar(100) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `start_date` varchar(100) NOT NULL,
  `end_date` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outreach`
--

INSERT INTO `outreach` (`id`, `project_title`, `location`, `duration`, `start_date`, `end_date`, `date_created`) VALUES
(1, 'Eye test & surgery', 'umudike, Abia State', '1 month', '', '', '2026-08-26 11:23:16'),
(6, 'hse', 'Apapa', '5 months', '', '', '2026-08-31 13:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `outreach_inventory`
--

CREATE TABLE `outreach_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Drug',
  `unit` varchar(50) DEFAULT 'Units',
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outreach_inventory`
--

INSERT INTO `outreach_inventory` (`id`, `item_name`, `category`, `unit`, `total_quantity`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol 500mg', 'Drug', 'Tablets', 0, '2026-08-28 13:30:09', '2026-09-01 13:01:07'),
(2, 'Digital Blood Pressure Monitor', 'Equipment', 'Units', 0, '2026-08-28 13:30:37', '2026-09-01 13:16:24'),
(3, 'para', 'Consumable', 'Capsules', 0, '2026-08-28 13:39:13', '2026-09-01 13:16:24'),
(4, 'testing', 'First Aid', 'Vials', 0, '2026-08-28 13:46:18', '2026-08-28 15:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `patient_medical_records`
--

CREATE TABLE `patient_medical_records` (
  `id` int(11) NOT NULL,
  `patient_name` varchar(150) NOT NULL,
  `patient_location` varchar(150) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `genotype` varchar(10) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `residential_address` text DEFAULT NULL,
  `next_of_kin_name` varchar(150) DEFAULT NULL,
  `next_of_kin_relationship` varchar(50) DEFAULT NULL,
  `next_of_kin_phone` varchar(30) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `intake_time` datetime NOT NULL,
  `release_time` datetime DEFAULT NULL,
  `blood_pressure` varchar(30) DEFAULT NULL,
  `temperature` varchar(20) DEFAULT NULL,
  `pulse_rate` varchar(20) DEFAULT NULL,
  `respiratory_rate` varchar(20) DEFAULT NULL,
  `oxygen_saturation` varchar(20) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `blood_sugar` varchar(30) DEFAULT NULL,
  `condition_on_admission` varchar(50) DEFAULT 'stable',
  `condition_on_release` varchar(50) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `diagnosis` text NOT NULL,
  `medical_notes` text DEFAULT NULL,
  `treatment_given` text DEFAULT NULL,
  `drugs_given` text DEFAULT NULL,
  `dosage_instructions` varchar(255) DEFAULT NULL,
  `attended_by` varchar(150) DEFAULT NULL,
  `follow_up_required` varchar(10) DEFAULT 'no',
  `follow_up_date` date DEFAULT NULL,
  `record_status` varchar(50) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_medical_records`
--

INSERT INTO `patient_medical_records` (`id`, `patient_name`, `patient_location`, `date_of_birth`, `gender`, `blood_group`, `genotype`, `phone_number`, `residential_address`, `next_of_kin_name`, `next_of_kin_relationship`, `next_of_kin_phone`, `allergies`, `medical_history`, `intake_time`, `release_time`, `blood_pressure`, `temperature`, `pulse_rate`, `respiratory_rate`, `oxygen_saturation`, `weight`, `height`, `blood_sugar`, `condition_on_admission`, `condition_on_release`, `symptoms`, `diagnosis`, `medical_notes`, `treatment_given`, `drugs_given`, `dosage_instructions`, `attended_by`, `follow_up_required`, `follow_up_date`, `record_status`, `created_at`) VALUES
(1, 'Francis Nwankwo', 'umudike, Abia State', '2026-07-31', 'Male', 'A-', 'AS', '07010010811', 'No 30 EMMA Avenue', 'Elizabeth sterling', 'brother', '3881595498', 'testing', 'testing', '2026-08-27 11:45:00', NULL, '99', '99', '50', '20', '11', '1', '22', '10', 'minor', 'improved', 'testing', 'testing', 'testing', 'testingg', 'Insulin Actrapid 100 IU/mL (Injection)', 'testing', 'Mrs Grace Equal', 'no', '2026-08-27', 'closed', '2026-08-27 10:48:10'),
(3, 'Elizabeth sterling', 'Apapa', '2026-09-26', 'Female', 'B-', 'AA', '3881595498', '36, Pemican Ct New York ON M9M 2Z3', 'Ebube bustdown', 'cousin', '08069815240', 'testing', 'testing', '2026-09-01 11:05:00', NULL, '1', '2', '3', '9', '3', '44', '10', '35', 'serious', NULL, 'testing', 'testing', 'testing', 'testing', 'Amoxil 300 mg (Capsule)', 'testing', 'Mrs Grace Equal', 'yes', '2026-09-03', 'open', '2026-09-01 10:05:11'),
(4, 'Space Terminal', 'umudike, Abia State', '2026-09-01', 'Other', 'A-', 'AS', '08069815240', 'No50 ifite awka', NULL, NULL, NULL, NULL, NULL, '2026-09-01 13:16:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'minor', NULL, NULL, 'yp', NULL, NULL, 'Amoxil 300 mg (Capsule), Insulin Actrapid 100 IU/mL (Injection), Panadol 500mg (Tablet)', NULL, 'Mrs Grace Equal', 'no', NULL, 'open', '2026-09-01 12:16:31'),
(5, 'Ebube bustdown', 'Apapa', '2026-09-01', 'Female', 'B+', NULL, '08069815240', 'No50 ifite awka', NULL, NULL, NULL, NULL, NULL, '2026-09-01 14:01:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'minor', NULL, NULL, 'zz', NULL, 'okay', 'Digital Blood Pressure Monitor (Units) (2), para (Capsules) (2), Paracetamol 500mg (Tablets)', NULL, 'Mrs Grace Equal', 'no', NULL, 'open', '2026-09-01 13:01:07');

-- --------------------------------------------------------

--
-- Table structure for table `referral_logs`
--

CREATE TABLE `referral_logs` (
  `id` int(11) NOT NULL,
  `staff_name` varchar(255) NOT NULL,
  `serial_id` varchar(100) NOT NULL,
  `ref_code` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referral_logs`
--

INSERT INTO `referral_logs` (`id`, `staff_name`, `serial_id`, `ref_code`, `created_at`) VALUES
(41, 'Francis Nwankwoo', '1000018', '6212', '2026-09-06 15:27:35'),
(42, 'Francis Nwankwoo', '1000018', '4869', '2026-09-08 09:54:50');

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
  `referred` varchar(20) DEFAULT NULL,
  `pdf` varchar(500) DEFAULT NULL,
  `record_status` enum('open','closed','under_treatment') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_medical_records`
--

INSERT INTO `staff_medical_records` (`id`, `staff_name`, `company`, `staff_branch`, `intake_time`, `release_time`, `diagnosis`, `symptoms`, `medical_notes`, `treatment_given`, `drugs_given`, `dosage_instructions`, `attended_by`, `department`, `condition_on_admission`, `condition_on_release`, `blood_pressure`, `temperature`, `pulse_rate`, `follow_up_required`, `follow_up_date`, `referred`, `pdf`, `record_status`, `created_at`, `updated_at`) VALUES
(12, 'Francis Nwankwoo', '', 'Fingesi Lekki phase1', '2026-08-02 12:22:00', '2026-08-04 13:30:00', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'information_technology', 'stable', 'improved', '20', '10', '50', 'no', '2026-08-05', '', '', 'under_treatment', '2026-08-12 11:23:21', '2026-08-12 12:30:24'),
(13, 'Ebube Nwankwo', '', 'Omisore Lekki Phase1', '2026-08-09 12:23:00', '2026-08-03 13:22:00', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'testing', 'human_resources', 'critical', 'improved', '10', '20', '15', 'no', '2026-08-11', '', '', 'open', '2026-08-12 11:24:07', '2026-08-12 12:22:59'),
(14, 'Ebube Nwankwo', '', 'Omisore Lekki Phase1', '2026-08-13 09:07:00', NULL, 'testing', 'HJNJN', '', 'MMM', 'MM', 'KKK', 'STELLA', 'human_resources', 'stable', 'improved', '', '', '', 'no', NULL, '', '', 'closed', '2026-08-13 08:08:38', '2026-08-15 21:39:00'),
(15, 'Ebube Nwankwo', '', 'Omisore Lekki Phase1', '2026-08-13 16:35:00', NULL, 'testing', 'testing', 'testing', 'testing', 'Panadol 500mg (Tablet)', 'testing', 'Mrs Grace Equall', 'human_resources', 'stable', 'improved', 'testing', 'testing', 'testing', 'no', '2026-08-13', '', '', 'open', '2026-08-13 15:35:39', '2026-08-14 08:08:43'),
(16, 'Francis Nwankwoo', 'Equal Logistics', 'Fingesi Lekki Phase1', '2026-08-13 16:36:00', NULL, 'testing', 'testing', 'testing', 'testing', 'Panadol 500mg (Tablet)', 'testing', 'Mrs Grace Equal', 'information_technology', 'stable', 'improved', '88', '788', '50', 'no', '2026-08-13', '', '', 'open', '2026-08-13 15:36:53', '2026-08-15 21:37:41'),
(17, 'Ebube Nwankwo', 'Upstream DC', 'Omisore Lekki Phase1', '2026-08-15 15:28:00', NULL, 'mm', 'mmam', 'mm', 'mm', 'Panadol 500mg (Tablet)', 'mm', 'Mrs Grace Equalllllll', 'human_resources', 'stable', 'stable', '10', '10', '10', 'no', '2026-08-21', '', NULL, 'closed', '2026-08-14 14:29:29', '2026-09-06 15:49:14'),
(18, 'Francis Nwankwoo', 'Equal Logistics', 'Fingesi Lekki Phase1', '2026-08-20 09:17:00', '2026-08-20 09:17:00', 'Diagnosis', 'Symptoms', 'Medical Notes', 'Treatment Given', 'Amoxil 300 mg (Capsule), Insulin Actrapid 100 IU/mL (Injection), Ventolin 100 mcg/dose (Inhaler)', 'Take all morning and night (once) .', 'Mrs Grace Equal', 'information_technology', 'stable', 'improved', '10', '10', '10', 'no', '2026-08-20', 'yes', 'Medical_Referral_4869_1788861290.pdf', 'closed', '2026-08-20 08:19:04', '2026-09-08 09:54:50');

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
  `passport` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `email`, `role`, `branch`, `passport`, `date_created`) VALUES
(1, 'Mrs Grace Equal', 'Grace50', 'aS/3sfyxvmlngXv9bFuClg==::CLBZ3FFgZOLZvZlO1Z1dbg==', 'chiefmedic@gmail.com', 'super-admin', 'Omisore Lekki Phase1', 'user_1_1787145922.jpg', '2026-05-06 13:55:23'),
(2, 'Mr Alfred Donold', 'alfredD', 'ONAkSl+HIfrcLNx12GpoJw==::kUY99HpbEc/Jt8mBa7Hx0A==', 'alfreddonald@gmail.com', 'staff', 'Fingesi Lekki Phase1', '', '2026-08-12 10:01:26'),
(3, 'Kofi Amuzu', 'kofi20', 'vu4S/K0JAZ0nPtc8n+V2Rg==::fiG9MjRNLduATefhhzPuTQ==', 'kofiequal@gmail.com', 'staff', 'Omisore Lekki Phase1', 'user_3_1787145738.webp', '2026-08-12 13:12:58'),
(6, 'john doe', 'johnny', 'E79SDg8GsMu1jYBz5wLDIQ==::wm2YPT7fMtTIp1mPgxHk/A==', 'marketedgee@proton.me', 'adhoc-user', 'umudike, Abia State', 'user_1787740588_5623.jpg', '2026-08-26 11:36:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch_allocations`
--
ALTER TABLE `branch_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `branch_stock_balance`
--
ALTER TABLE `branch_stock_balance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_item_unique` (`branch_name`,`item_id`),
  ADD KEY `item_id` (`item_id`);

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
-- Indexes for table `outreach`
--
ALTER TABLE `outreach`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `outreach_inventory`
--
ALTER TABLE `outreach_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_medical_records`
--
ALTER TABLE `patient_medical_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_logs`
--
ALTER TABLE `referral_logs`
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
-- AUTO_INCREMENT for table `branch_allocations`
--
ALTER TABLE `branch_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `branch_stock_balance`
--
ALTER TABLE `branch_stock_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `drugs_allocations`
--
ALTER TABLE `drugs_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `drugs_master`
--
ALTER TABLE `drugs_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `drugs_stock_logs`
--
ALTER TABLE `drugs_stock_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `outreach`
--
ALTER TABLE `outreach`
  MODIFY `id` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `outreach_inventory`
--
ALTER TABLE `outreach_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patient_medical_records`
--
ALTER TABLE `patient_medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `referral_logs`
--
ALTER TABLE `referral_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_medical_records`
--
ALTER TABLE `staff_medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `branch_allocations`
--
ALTER TABLE `branch_allocations`
  ADD CONSTRAINT `branch_allocations_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `outreach_inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_stock_balance`
--
ALTER TABLE `branch_stock_balance`
  ADD CONSTRAINT `branch_stock_balance_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `outreach_inventory` (`id`) ON DELETE CASCADE;

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
