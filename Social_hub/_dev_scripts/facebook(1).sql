-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2024 at 06:00 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `facebook`
--

-- --------------------------------------------------------

--
-- Table structure for table `friend_request`
--

CREATE TABLE `friend_request` (
  `request_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `request_status` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `friend_request`
--

INSERT INTO `friend_request` (`request_id`, `sender_id`, `receiver_id`, `request_status`, `timestamp`) VALUES
(6, 8, 9, 'friend', '2024-04-12 07:30:56'),
(7, 10, 9, 'pending', '2024-04-12 08:17:29'),
(8, 10, 8, 'pending', '2024-04-12 08:17:35');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `msg_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `msg_contant` text NOT NULL,
  `is_read` varchar(255) NOT NULL,
  `msg_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`msg_id`, `sender_id`, `receiver_id`, `msg_contant`, `is_read`, `msg_time`) VALUES
(12, 9, 8, 'Hello', '1', '2024-04-12 08:35:55'),
(13, 9, 8, 'Good Morning!', '1', '2024-04-12 08:36:10'),
(14, 8, 9, 'Hello', '1', '2024-04-12 08:39:17'),
(15, 8, 9, 'GM', '1', '2024-04-12 08:39:26');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_srno` int(11) NOT NULL,
  `response_who_show` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `is_read` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_srno`, `response_who_show`, `message`, `timestamp`, `is_read`) VALUES
(27, 9, 8, 'has accepted your friend request.', '2024-04-12 08:38:36', '1'),
(28, 10, 9, 'has sent you friend request.', '2024-04-12 08:29:31', '1'),
(29, 10, 8, 'has sent you friend request.', '2024-04-12 08:38:36', '1'),
(30, 10, 9, 'is comment on your post.', '2024-04-12 08:29:31', '1');

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `post_id` int(11) NOT NULL,
  `post_user_srno` int(11) NOT NULL,
  `post_caption` text NOT NULL,
  `post_image` varchar(255) NOT NULL,
  `media_type` varchar(255) NOT NULL,
  `post_create` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`post_id`, `post_user_srno`, `post_caption`, `post_image`, `media_type`, `post_create`) VALUES
(9, 9, 'Test Post', 'nature-3082832__340(1).jpg', 'image/jpeg', '2024-04-12 07:25:06'),
(10, 8, 'Hello', 'wall.jpeg', 'image/jpeg', '2024-04-12 07:29:14'),
(11, 10, 'Test1 Post', 'video1.mp4', 'video/mp4', '2024-04-12 08:21:48'),
(12, 10, 'This is Caption!', 'second_img.jpg', 'image/jpeg', '2024-04-12 08:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `post_comment`
--

CREATE TABLE `post_comment` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `comment_user_srno` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `post_comment`
--

INSERT INTO `post_comment` (`comment_id`, `post_id`, `comment_user_srno`, `comment_text`, `timestamp`) VALUES
(16, 9, 10, 'This is comment!', '2024-04-12 08:29:03');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_srno` int(11) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`id`, `post_id`, `user_srno`, `timestamp`) VALUES
(14, 12, 10, '2024-04-12 08:28:45'),
(15, 11, 10, '2024-04-12 08:28:48'),
(16, 10, 10, '2024-04-12 08:28:50'),
(17, 9, 10, '2024-04-12 08:28:52'),
(18, 11, 8, '2024-04-12 08:41:51'),
(19, 10, 8, '2024-04-12 08:41:53'),
(20, 9, 8, '2024-04-12 08:41:56');

-- --------------------------------------------------------

--
-- Table structure for table `story`
--

CREATE TABLE `story` (
  `story_id` int(11) NOT NULL,
  `user_create_by` int(11) NOT NULL,
  `story_caption` varchar(255) NOT NULL,
  `story_media` varchar(255) NOT NULL,
  `story_type` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `story`
--

INSERT INTO `story` (`story_id`, `user_create_by`, `story_caption`, `story_media`, `story_type`, `created_at`) VALUES
(10, 9, 'Test Story', 'story_media/nature-3082832__340.jpg', ' image', '2024-04-12 07:25:48'),
(11, 8, 'Good Morning', 'story_media/IMG_0206.png', ' image', '2024-04-12 07:30:33'),
(12, 10, 'Test1 Story', 'story_media/video1.mp4', ' video', '2024-04-12 08:21:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_data`
--

CREATE TABLE `user_data` (
  `srno` int(11) NOT NULL,
  `user_firstname` varchar(255) NOT NULL,
  `user_surname` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_dob` varchar(255) NOT NULL,
  `user_gender` varchar(255) NOT NULL,
  `user_image` varchar(255) NOT NULL,
  `user_role` varchar(255) NOT NULL DEFAULT 'user',
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_data`
--

INSERT INTO `user_data` (`srno`, `user_firstname`, `user_surname`, `user_id`, `user_password`, `user_dob`, `user_gender`, `user_image`, `user_role`, `timestamp`) VALUES
(4, 'admin', '', 'admin@admin.com', 'admin', '', '', '', 'admin', '2024-02-05 17:21:51'),
(8, 'Vandan', 'Desai', '1234567890', '$2y$10$5QsK8ujeemHeL2WBDJv3lOXF6GLc02XrONRG/F7cb0rc2TLp4p24e', '2003-04-22', 'Male', 'wall.jpeg', 'user', '2024-04-12 07:16:00'),
(9, 'Test', 'User', 'test@test.com', '$2y$10$w4701xFKAR.8YKMKvU2X5eg7wx0QdzhImR1TMxZQpX5NoNaOCl.iC', '1998-04-28', 'Female', 'nature-3082832__340.jpg', 'user', '2024-04-12 07:23:02'),
(10, 'Test1', 'User1', 'test1@test1.com', '$2y$10$HFUa.G0OuwUzLxTFGM31he2Vc8CQRYMQ7DkgJKuDRtY4733zPN3/.', '2004-04-21', 'Male', '1.avif', 'user', '2024-04-12 08:15:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `friend_request`
--
ALTER TABLE `friend_request`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`msg_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `post_comment`
--
ALTER TABLE `post_comment`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `story`
--
ALTER TABLE `story`
  ADD PRIMARY KEY (`story_id`);

--
-- Indexes for table `user_data`
--
ALTER TABLE `user_data`
  ADD PRIMARY KEY (`srno`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `friend_request`
--
ALTER TABLE `friend_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `post_comment`
--
ALTER TABLE `post_comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `story`
--
ALTER TABLE `story`
  MODIFY `story_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_data`
--
ALTER TABLE `user_data`
  MODIFY `srno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
