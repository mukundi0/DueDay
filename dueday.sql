/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



--
-- Database: `dueday`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `Achievement_ID` int(11) NOT NULL,
  `Achievement_Description` text NOT NULL,
  `Achievement_Points` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`Achievement_ID`, `Achievement_Description`, `Achievement_Points`) VALUES
(1, 'Welcome Aboard! - Created an account.', 10),
(2, 'First Steps - Submitted your first assignment.', 25),
(3, 'Civic Duty - Voted in your first poll.', 15),
(4, 'Event-Goer - RSVP\'d for an event.', 20);

-- --------------------------------------------------------

--
-- Table structure for table `admin_data`
--

CREATE TABLE `admin_data` (
  `Data_ID` int(11) NOT NULL,
  `Data_Description` varchar(255) NOT NULL,
  `Data_Value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `Announcement_ID` int(11) NOT NULL,
  `Announcement_Title` varchar(100) NOT NULL,
  `Announcement_Description` text DEFAULT NULL,
  `Announcement_Priority` int(11) NOT NULL,
  `Creator_User_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_user`
--

CREATE TABLE `announcement_user` (
  `Announcement_User_ID` int(11) NOT NULL,
  `Announcement_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `Assignment_ID` int(11) NOT NULL,
  `Assignment_Creator_ID` int(11) NOT NULL,
  `Class_ID` int(11) DEFAULT NULL,
  `Assignment_Title` varchar(100) NOT NULL,
  `Assignment_Description` text DEFAULT NULL,
  `Assignment_DueDate` datetime NOT NULL,
  `Assignment_Marks` int(11) DEFAULT NULL,
  `Assignment_Instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`Assignment_ID`, `Assignment_Creator_ID`, `Class_ID`, `Assignment_Title`, `Assignment_Description`, `Assignment_DueDate`, `Assignment_Marks`, `Assignment_Instructions`) VALUES
(1, 5, NULL, 'OOP project', 'finish implementing all the API and libaries ', '2025-07-11 12:30:00', 20, 'Use the provided resources'),
(2, 5, 2, 'Pipelining', 'Answer the three questions shared in class', '2025-06-20 08:15:00', 30, 'Ensure you do a thorough research'),
(3, 5, 1, 'Java Takeaway CAT', 'The CAT will be available for viewing on Elearning', '2025-06-27 19:47:00', 20, '');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_comments`
--

CREATE TABLE `assignment_comments` (
  `Comment_ID` int(11) NOT NULL,
  `Assignment_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Comment_Text` text NOT NULL,
  `Comment_Date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_comments`
--

INSERT INTO `assignment_comments` (`Comment_ID`, `Assignment_ID`, `User_ID`, `Comment_Text`, `Comment_Date`) VALUES
(2, 2, 4, 'We are getting to many assignments we need a reduction', '2025-06-25 19:40:23'),
(3, 1, 8, 'Testing', '2025-06-25 20:34:29');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submission_data`
--

CREATE TABLE `assignment_submission_data` (
  `Submission_ID` int(11) NOT NULL,
  `Assignment_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Submission_Date` datetime NOT NULL,
  `File_Path` varchar(255) NOT NULL,
  `Notes` text DEFAULT NULL,
  `Grade` varchar(10) DEFAULT NULL,
  `Feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_submission_data`
--

INSERT INTO `assignment_submission_data` (`Submission_ID`, `Assignment_ID`, `User_ID`, `Submission_Date`, `File_Path`, `Notes`, `Grade`, `Feedback`) VALUES
(3, 1, 7, '2025-06-19 20:25:12', 'uploads/submissions/assign1_user7_1750353912_ciscolabnumberone.pkt', 'N/A', '10', 'You need to get a better understanding of classes'),
(5, 2, 6, '2025-06-20 00:59:08', 'uploads/submissions/assign2_user6_1750370348_README.md', '', NULL, NULL),
(6, 1, 6, '2025-06-20 00:59:49', 'uploads/submissions/assign1_user6_1750370389_test.py', '', NULL, NULL),
(7, 1, 4, '2025-06-20 01:00:39', 'uploads/submissions/assign1_user4_1750370439_test.py', '', '8', ''),
(8, 2, 4, '2025-06-20 01:00:45', 'uploads/submissions/assign2_user4_1750370445_test.py', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `Class_ID` int(11) NOT NULL,
  `Class_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`Class_ID`, `Class_Name`) VALUES
(1, 'Object Oriented Programming 2 '),
(2, 'Computer Organization And Architecture'),
(3, 'Probability And Statistics 1'),
(4, 'Web Application Development'),
(5, 'Maisha Program Ladies'),
(6, 'Maisha Program Gents'),
(7, 'Principles of Ethics'),
(8, 'Data Structures And Algorithms'),
(9, 'Spanish 1'),
(10, 'French 1'),
(11, 'World Civilization 1 '),
(12, 'Chinese 1');

-- --------------------------------------------------------

--
-- Table structure for table `class_schedule`
--

CREATE TABLE `class_schedule` (
  `Entry_ID` int(11) NOT NULL,
  `Class_ID` int(11) NOT NULL,
  `Venue_ID` int(11) NOT NULL,
  `Day_Of_Week` int(11) NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_schedule`
--

INSERT INTO `class_schedule` (`Entry_ID`, `Class_ID`, `Venue_ID`, `Day_Of_Week`, `Start_Time`, `End_Time`) VALUES
(3, 2, 12, 3, '08:15:00', '10:15:00'),
(4, 3, 13, 5, '08:15:00', '10:15:00'),
(5, 7, 19, 1, '08:15:00', '10:15:00'),
(6, 6, 20, 1, '11:15:00', '12:15:00'),
(7, 4, 18, 2, '12:15:00', '14:15:00'),
(8, 9, 15, 2, '14:15:00', '16:15:00'),
(9, 1, 18, 3, '15:15:00', '17:15:00'),
(10, 8, 6, 3, '10:15:00', '12:15:00'),
(11, 9, 22, 4, '09:15:00', '11:15:00'),
(12, 8, 22, 4, '12:15:00', '14:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `Event_ID` int(11) NOT NULL,
  `Event_Name` varchar(100) NOT NULL,
  `Event_Description` text DEFAULT NULL,
  `Event_Date` datetime NOT NULL,
  `Venue_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`Event_ID`, `Event_Name`, `Event_Description`, `Event_Date`, `Venue_ID`) VALUES
(2, 'Maisha Vibes', 'Meeting point ', '2025-06-28 08:30:00', 2),
(3, 'Tree Planting', 'A day to be fully involved in a project that helps our environment\r\nCome and be bold for a better greener life', '2025-06-26 10:30:00', 23);

-- --------------------------------------------------------

--
-- Table structure for table `event_attendee_data`
--

CREATE TABLE `event_attendee_data` (
  `Attendee_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Event_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_attendee_data`
--

INSERT INTO `event_attendee_data` (`Attendee_ID`, `User_ID`, `Event_ID`) VALUES
(2, 5, 2),
(3, 8, 2),
(4, 5, 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `Notification_ID` int(11) NOT NULL,
  `Notification_Content` text NOT NULL,
  `Notification_Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`Notification_ID`, `Notification_Content`, `Notification_Date`) VALUES
(1, 'Enrollment: Enrollment and class registration ends on June 31st', '2025-06-20 00:01:28'),
(2, 'Maintanance: There will be a scheduled downtime between 7:00 AM and 9:00 PM on Wednesday', '2025-06-24 13:43:51');

-- --------------------------------------------------------

--
-- Table structure for table `notification_user`
--

CREATE TABLE `notification_user` (
  `Notification_User_ID` int(11) NOT NULL,
  `Notification_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_user`
--

INSERT INTO `notification_user` (`Notification_User_ID`, `Notification_ID`, `User_ID`) VALUES
(1, 1, 1),
(2, 1, 5),
(3, 1, 2),
(4, 1, 6),
(5, 1, 4),
(6, 1, 3),
(7, 1, 7),
(8, 2, 1),
(9, 2, 5),
(10, 2, 2),
(11, 2, 6),
(12, 2, 4),
(13, 2, 7),
(14, 2, 3),
(15, 2, 8);

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE `polls` (
  `Poll_ID` int(11) NOT NULL,
  `Poll_Title` varchar(100) NOT NULL,
  `Poll_Description` text DEFAULT NULL,
  `Class_ID` int(11) DEFAULT NULL,
  `Expires_At` datetime NOT NULL,
  `Status` varchar(20) NOT NULL,
  `Is_Anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `Allow_Multiple_Choices` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `polls`
--

INSERT INTO `polls` (`Poll_ID`, `Poll_Title`, `Poll_Description`, `Class_ID`, `Expires_At`, `Status`, `Is_Anonymous`, `Allow_Multiple_Choices`) VALUES
(1, 'Spanish Make up Cat', 'For students who missed the previous CAT', NULL, '2025-06-21 07:00:00', 'Active', 0, 0),
(2, 'Who would you like as the new module leader', '', 7, '2025-06-24 12:01:00', 'Active', 0, 0),
(3, 'CAT replacement', 'What do you prefer in replacement of the CAT 2', 2, '2025-06-27 08:45:00', 'Active', 0, 0),
(4, 'Submission Date', '', 1, '2025-06-27 12:00:00', 'Active', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `poll_data`
--

CREATE TABLE `poll_data` (
  `User_ID` int(11) NOT NULL,
  `Poll_ID` int(11) NOT NULL,
  `Option_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll_data`
--

INSERT INTO `poll_data` (`User_ID`, `Poll_ID`, `Option_ID`) VALUES
(4, 1, 1),
(5, 1, 1),
(6, 1, 1),
(7, 1, 2),
(4, 2, 3),
(5, 2, 3),
(6, 2, 3),
(7, 2, 3),
(6, 3, 6),
(7, 3, 6),
(8, 3, 6),
(4, 3, 7),
(5, 4, 8);

-- --------------------------------------------------------

--
-- Table structure for table `poll_options`
--

CREATE TABLE `poll_options` (
  `Option_ID` int(11) NOT NULL,
  `Poll_ID` int(11) NOT NULL,
  `Option_Text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll_options`
--

INSERT INTO `poll_options` (`Option_ID`, `Poll_ID`, `Option_Text`) VALUES
(1, 1, 'June 21'),
(2, 1, 'June 28'),
(3, 2, 'Alvin Murithi'),
(4, 2, 'Ishamel Tom'),
(5, 3, 'Lab Project'),
(6, 3, 'Takeaway Assignment'),
(7, 3, 'Just do the CAT'),
(8, 4, 'Physical'),
(9, 4, 'Online');

-- --------------------------------------------------------

--
-- Table structure for table `priority`
--

CREATE TABLE `priority` (
  `Priority_ID` int(11) NOT NULL,
  `Priority_Type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `priority`
--

INSERT INTO `priority` (`Priority_ID`, `Priority_Type`) VALUES
(1, 'Low'),
(2, 'Normal'),
(3, 'High'),
(4, 'Urgent');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `Role_ID` int(11) NOT NULL,
  `Role_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`Role_ID`, `Role_Name`) VALUES
(1, 'Module Leader'),
(2, 'Student'),
(3, 'Event Coordinator'),
(4, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `Timetable_ID` int(11) NOT NULL,
  `Timetable_Url` varchar(255) DEFAULT NULL,
  `Timetable_BLOB` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_ID` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `F_Name` varchar(50) NOT NULL,
  `L_Name` varchar(50) NOT NULL,
  `Role_ID` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_ID`, `Email`, `Password`, `F_Name`, `L_Name`, `Role_ID`, `status`) VALUES
(1, 'leader@dueday.com', 'hashed_password', 'Natalie', 'Leader', 1, 'inactive'),
(2, 'student@dueday.com', 'hashed_password', 'Alvin', 'Student', 2, 'inactive'),
(3, 'ialvinmurithi@gmail.com', '$2y$10$.oxCuyOQbY0kiiwb1fFxN.AgnmmtQ1ARV9Zh1jdX.mfC0tnj2ZxlG', 'Alvin', 'Murithi', 4, 'active'),
(4, 'travism@gmail.com', '$2y$10$51w.mq/2Ya7Hj5xeyAAU9eq//OLTm..isTuxZ3dRbq6/7ybzUbwuO', 'Travis', 'Mutungi', 3, 'active'),
(5, 'dmuriuki@gmail.com', '$2y$10$O9m8z0oyYlkNr4DF.Y2ote0eMeT5eq1x4VDRRBaNtZ.VgQUJi4KCe', 'Daniel', 'Muriuki', 1, 'active'),
(6, 'erodriguez@gmail.com', '$2y$10$Ql59.CzaaXS1bHDe7zg45OqwlrQPNAeWNclu5xBffdnMweH3SmZze', 'Eva', 'Rodriguez', 2, 'active'),
(7, 'nataliec@gmail.com', '$2y$10$7x1XD.NkUzGj.mkQnGkOGOgcUphjhNEz.1ip5DnVw6cvT7eSQNE86', 'Natalie', 'Chelangat', 3, 'active'),
(8, 'admin@gmail.com', '$2y$10$kN7PHdILRs/OFHWScNaJr.9ATlMVaTok/Sl4bxbXw.ei.6TLDpCdm', 'Dueday', 'Admin', 4, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `User_ID` int(11) NOT NULL,
  `Achievement_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_achievements`
--

INSERT INTO `user_achievements` (`User_ID`, `Achievement_ID`) VALUES
(3, 1),
(4, 1),
(4, 2),
(4, 3),
(5, 1),
(5, 3),
(5, 4),
(6, 1),
(6, 2),
(6, 3),
(7, 1),
(7, 2),
(7, 3),
(8, 1),
(8, 2),
(8, 3),
(8, 4);

-- --------------------------------------------------------

--
-- Table structure for table `user_classes`
--

CREATE TABLE `user_classes` (
  `User_ID` int(11) NOT NULL,
  `Class_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_classes`
--

INSERT INTO `user_classes` (`User_ID`, `Class_ID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 7),
(1, 8),
(1, 9),
(1, 12),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 12),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 12),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 6),
(4, 7),
(4, 8),
(4, 10),
(4, 12),
(5, 1),
(5, 2),
(5, 3),
(5, 4),
(5, 6),
(5, 7),
(5, 8),
(5, 10),
(5, 12),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(6, 7),
(6, 8),
(6, 9),
(6, 12),
(7, 1),
(7, 2),
(7, 3),
(7, 4),
(7, 7),
(7, 8),
(7, 12),
(8, 1),
(8, 2),
(8, 3),
(8, 4),
(8, 6),
(8, 7),
(8, 8),
(8, 9),
(8, 10),
(8, 11),
(8, 12);

-- --------------------------------------------------------

--
-- Table structure for table `user_read_comments`
--

CREATE TABLE `user_read_comments` (
  `User_ID` int(11) NOT NULL,
  `Comment_ID` int(11) NOT NULL,
  `Read_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_read_comments`
--

INSERT INTO `user_read_comments` (`User_ID`, `Comment_ID`, `Read_At`) VALUES
(4, 2, '2025-06-25 17:06:11'),
(4, 3, '2025-06-25 17:35:23'),
(8, 2, '2025-06-25 17:34:42'),
(8, 3, '2025-06-25 17:34:48');

-- --------------------------------------------------------

--
-- Table structure for table `user_timetable`
--

CREATE TABLE `user_timetable` (
  `User_Timetable_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Timetable_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `Venue_ID` int(11) NOT NULL,
  `Venue_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`Venue_ID`, `Venue_Name`) VALUES
(1, 'Microsoft Auditorium'),
(2, 'Main Auditorium'),
(3, 'LT 1'),
(4, 'LT 2'),
(5, 'LT 3'),
(6, 'LT 4'),
(7, 'MSB 1'),
(8, 'MSB 2'),
(9, 'MSB 3'),
(10, 'MSB 4'),
(11, 'Blue Sky'),
(12, 'Masinga Lab'),
(13, 'MSB 6'),
(14, 'MSB 5'),
(15, 'MSB 7'),
(16, 'MSB 8'),
(17, 'MSB 9'),
(18, 'MSB 10'),
(19, 'SLS Shaba'),
(20, 'RM 4'),
(21, 'STMB F2-05'),
(22, 'Virtual'),
(23, 'Sports Complex');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`Achievement_ID`);

--
-- Indexes for table `admin_data`
--
ALTER TABLE `admin_data`
  ADD PRIMARY KEY (`Data_ID`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`Announcement_ID`),
  ADD KEY `Announcement_Priority` (`Announcement_Priority`),
  ADD KEY `Creator_User_ID` (`Creator_User_ID`);

--
-- Indexes for table `announcement_user`
--
ALTER TABLE `announcement_user`
  ADD PRIMARY KEY (`Announcement_User_ID`),
  ADD KEY `Announcement_ID` (`Announcement_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`Assignment_ID`),
  ADD KEY `Assignment_Creator_ID` (`Assignment_Creator_ID`),
  ADD KEY `fk_assignment_class` (`Class_ID`);

--
-- Indexes for table `assignment_comments`
--
ALTER TABLE `assignment_comments`
  ADD PRIMARY KEY (`Comment_ID`),
  ADD KEY `Assignment_ID` (`Assignment_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `assignment_submission_data`
--
ALTER TABLE `assignment_submission_data`
  ADD PRIMARY KEY (`Submission_ID`),
  ADD KEY `Assignment_ID` (`Assignment_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`Class_ID`);

--
-- Indexes for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD PRIMARY KEY (`Entry_ID`),
  ADD KEY `Class_ID` (`Class_ID`),
  ADD KEY `Venue_ID` (`Venue_ID`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`Event_ID`),
  ADD KEY `Venue_ID` (`Venue_ID`);

--
-- Indexes for table `event_attendee_data`
--
ALTER TABLE `event_attendee_data`
  ADD PRIMARY KEY (`Attendee_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Event_ID` (`Event_ID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Notification_ID`);

--
-- Indexes for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD PRIMARY KEY (`Notification_User_ID`),
  ADD KEY `Notification_ID` (`Notification_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`Poll_ID`),
  ADD KEY `fk_poll_class` (`Class_ID`);

--
-- Indexes for table `poll_data`
--
ALTER TABLE `poll_data`
  ADD PRIMARY KEY (`User_ID`,`Poll_ID`),
  ADD KEY `Poll_ID` (`Poll_ID`),
  ADD KEY `Option_ID` (`Option_ID`);

--
-- Indexes for table `poll_options`
--
ALTER TABLE `poll_options`
  ADD PRIMARY KEY (`Option_ID`),
  ADD KEY `Poll_ID` (`Poll_ID`);

--
-- Indexes for table `priority`
--
ALTER TABLE `priority`
  ADD PRIMARY KEY (`Priority_ID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`Role_ID`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`Timetable_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`User_ID`,`Achievement_ID`),
  ADD KEY `Achievement_ID` (`Achievement_ID`);

--
-- Indexes for table `user_classes`
--
ALTER TABLE `user_classes`
  ADD PRIMARY KEY (`User_ID`,`Class_ID`),
  ADD KEY `Class_ID` (`Class_ID`);

--
-- Indexes for table `user_read_comments`
--
ALTER TABLE `user_read_comments`
  ADD PRIMARY KEY (`User_ID`,`Comment_ID`),
  ADD KEY `fk_read_comments_comment` (`Comment_ID`);

--
-- Indexes for table `user_timetable`
--
ALTER TABLE `user_timetable`
  ADD PRIMARY KEY (`User_Timetable_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Timetable_ID` (`Timetable_ID`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`Venue_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `Achievement_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_data`
--
ALTER TABLE `admin_data`
  MODIFY `Data_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `Announcement_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcement_user`
--
ALTER TABLE `announcement_user`
  MODIFY `Announcement_User_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `Assignment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assignment_comments`
--
ALTER TABLE `assignment_comments`
  MODIFY `Comment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assignment_submission_data`
--
ALTER TABLE `assignment_submission_data`
  MODIFY `Submission_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `Class_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `class_schedule`
--
ALTER TABLE `class_schedule`
  MODIFY `Entry_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `Event_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_attendee_data`
--
ALTER TABLE `event_attendee_data`
  MODIFY `Attendee_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Notification_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notification_user`
--
ALTER TABLE `notification_user`
  MODIFY `Notification_User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `polls`
--
ALTER TABLE `polls`
  MODIFY `Poll_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `poll_options`
--
ALTER TABLE `poll_options`
  MODIFY `Option_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `priority`
--
ALTER TABLE `priority`
  MODIFY `Priority_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `Role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `Timetable_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_timetable`
--
ALTER TABLE `user_timetable`
  MODIFY `User_Timetable_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `Venue_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`Announcement_Priority`) REFERENCES `priority` (`Priority_ID`),
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`Creator_User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `announcement_user`
--
ALTER TABLE `announcement_user`
  ADD CONSTRAINT `announcement_user_ibfk_1` FOREIGN KEY (`Announcement_ID`) REFERENCES `announcements` (`Announcement_ID`),
  ADD CONSTRAINT `announcement_user_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`Assignment_Creator_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `fk_assignment_class` FOREIGN KEY (`Class_ID`) REFERENCES `classes` (`Class_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `assignment_comments`
--
ALTER TABLE `assignment_comments`
  ADD CONSTRAINT `assignment_comments_ibfk_1` FOREIGN KEY (`Assignment_ID`) REFERENCES `assignments` (`Assignment_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_comments_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submission_data`
--
ALTER TABLE `assignment_submission_data`
  ADD CONSTRAINT `assignment_submission_data_ibfk_1` FOREIGN KEY (`Assignment_ID`) REFERENCES `assignments` (`Assignment_ID`),
  ADD CONSTRAINT `assignment_submission_data_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD CONSTRAINT `class_schedule_ibfk_1` FOREIGN KEY (`Class_ID`) REFERENCES `classes` (`Class_ID`),
  ADD CONSTRAINT `class_schedule_ibfk_2` FOREIGN KEY (`Venue_ID`) REFERENCES `venues` (`Venue_ID`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`Venue_ID`) REFERENCES `venues` (`Venue_ID`);

--
-- Constraints for table `event_attendee_data`
--
ALTER TABLE `event_attendee_data`
  ADD CONSTRAINT `event_attendee_data_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `event_attendee_data_ibfk_2` FOREIGN KEY (`Event_ID`) REFERENCES `events` (`Event_ID`);

--
-- Constraints for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD CONSTRAINT `notification_user_ibfk_1` FOREIGN KEY (`Notification_ID`) REFERENCES `notifications` (`Notification_ID`),
  ADD CONSTRAINT `notification_user_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `polls`
--
ALTER TABLE `polls`
  ADD CONSTRAINT `fk_poll_class` FOREIGN KEY (`Class_ID`) REFERENCES `classes` (`Class_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `poll_data`
--
ALTER TABLE `poll_data`
  ADD CONSTRAINT `poll_data_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `poll_data_ibfk_2` FOREIGN KEY (`Poll_ID`) REFERENCES `polls` (`Poll_ID`),
  ADD CONSTRAINT `poll_data_ibfk_3` FOREIGN KEY (`Option_ID`) REFERENCES `poll_options` (`Option_ID`);

--
-- Constraints for table `poll_options`
--
ALTER TABLE `poll_options`
  ADD CONSTRAINT `poll_options_ibfk_1` FOREIGN KEY (`Poll_ID`) REFERENCES `polls` (`Poll_ID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `role` (`Role_ID`);

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`Achievement_ID`) REFERENCES `achievements` (`Achievement_ID`);

--
-- Constraints for table `user_classes`
--
ALTER TABLE `user_classes`
  ADD CONSTRAINT `user_classes_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `user_classes_ibfk_2` FOREIGN KEY (`Class_ID`) REFERENCES `classes` (`Class_ID`);

--
-- Constraints for table `user_read_comments`
--
ALTER TABLE `user_read_comments`
  ADD CONSTRAINT `fk_read_comments_comment` FOREIGN KEY (`Comment_ID`) REFERENCES `assignment_comments` (`Comment_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_read_comments_user` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) ON DELETE CASCADE;

--
-- Constraints for table `user_timetable`
--
ALTER TABLE `user_timetable`
  ADD CONSTRAINT `user_timetable_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `user_timetable_ibfk_2` FOREIGN KEY (`Timetable_ID`) REFERENCES `timetable` (`Timetable_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
