SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE `AUX_posts_categories` (
  `posts_post_id` int(11) NOT NULL,
  `categories_category_id` int(11) NOT NULL,
  PRIMARY KEY (`posts_post_id`,`categories_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `slug` varchar(250) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `slug_UNIQUE` (`slug`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `posted` datetime NOT NULL,
  `post_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `screenname` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `url` varchar(250) DEFAULT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `posted` (`posted`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `posted` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`post_id`),
  UNIQUE KEY `slug_UNIQUE` (`slug`),
  KEY `status` (`status`),
  KEY `posted` (`posted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `sessions` (
  `session_id` int(10) NOT NULL AUTO_INCREMENT,
  `php_sid` varchar(32) NOT NULL,
  `logged_in` tinyint(1) NOT NULL,
  `user_id` int(10) NOT NULL,
  `activity` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `php_sid` (`php_sid`),
  KEY `activity` (`created`,`activity`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `session_variables` (
  `var_id` int(10) NOT NULL AUTO_INCREMENT,
  `session_id` int(10) NOT NULL,
  `var_name` varchar(64) NOT NULL,
  `var_value` text NOT NULL,
  PRIMARY KEY (`var_id`),
  KEY `session_id` (`session_id`),
  KEY `var_name` (`var_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `registration` datetime NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(250) NOT NULL,
  `pwd` varchar(40) NOT NULL,
  `salt` varchar(3) NOT NULL,
  `fullname` varchar(250) DEFAULT NULL,
  `screenname` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `users` (`user_id`, `registration`, `last_login`, `username`, `email`, `pwd`, `salt`, `fullname`, `screenname`) VALUES
(1, '2011-12-15 22:24:00', NULL, 'admin', 'admin@dreamsincode.com', 'f3e4b7e7900f69c4567bc6d22389872219f477d9', 'mvo', 'Administrator', 'Administrator');
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
