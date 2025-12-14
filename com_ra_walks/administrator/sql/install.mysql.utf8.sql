# 07/07/23 update ra_walks: add meeting_w3w, increase length of contact_email, delete ra_snapshot
# 28/07/23 delete logfile, groups_audit, walks_feedback and walks_follow
# 14/08/23 default state to unpublished
# 21/08/23 contact_tel1 to 150
# 21/08/23 include logfile (for ra_read_feed)
 # 11/09/23 CB Meeting details to TEXT
CREATE TABLE IF NOT EXISTS `#__ra_walks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `walk_id` int DEFAULT '0',
  `group_code` varchar(4) NOT NULL,
  `walk_date` date DEFAULT NULL,
  `title` varchar(120) NOT NULL,
  `description` text,
  `additional_notes` varchar(255) DEFAULT '',
  `circular_or_linear` char(1) DEFAULT 'C',
  `restriction` varchar(1) DEFAULT '',
  `difficulty` varchar(10) DEFAULT '',
  `distance_miles` decimal(3,1) DEFAULT '0.0',
  `distance_km` decimal(3,1) DEFAULT '0.0',
  `pace` varchar(50) DEFAULT '',
  `ascent_feet` varchar(10) DEFAULT '',
  `ascent_metres` varchar(10) DEFAULT '',

  `start_postcode` varchar(8) DEFAULT '',
  `start_longitude` decimal(10,8) NOT NULL DEFAULT '0.00000000',
  `start_latitude` decimal(10,8) NOT NULL DEFAULT '0.00000000',
  `start_gridref` varchar(12) DEFAULT '',
  `start_w3w` varchar(12) DEFAULT '',
  `start_details` TEXT,
  `start_time` varchar(5) DEFAULT '',

  `meeting_postcode` varchar(8) DEFAULT '',
  `meeting_longitude` decimal(14,12) NOT NULL DEFAULT '0.00000000',
  `meeting_latitude` decimal(14,12) NOT NULL DEFAULT '0.00000000',
  `meeting_gridref` varchar(12) DEFAULT '',
  `meeting_time` varchar(5) DEFAULT '',
  `meeting_details` TEXT,


  `walking_time` varchar(5) DEFAULT '',
  `finishing_time` varchar(5) DEFAULT '',
  `contact_display_name` varchar(100) DEFAULT '',
  `contact_membership_no` int NOT NULL DEFAULT '0',
  `contact_email` varchar(255) DEFAULT '',
  `contact_tel1` varchar(150) DEFAULT '',
  `contact_tel2` varchar(15) DEFAULT '',
  `contact_is_walk_leader` char(1) DEFAULT 'Y',
  `walk_leader` varchar(30) DEFAULT '',

  `grade_local` varchar(10) DEFAULT '',
  `route_id` varchar(10) DEFAULT '0',
  `state` int NOT NULL DEFAULT '0',
  `notes` varchar(255) DEFAULT '',
  `published` tinyint NOT NULL DEFAULT '1',
  `leader_user_id` int NOT NULL DEFAULT '0',
  `finish_time` varchar(5) NOT NULL DEFAULT '0',
  `duration` int NOT NULL DEFAULT '0',
  `max_walkers` int NOT NULL DEFAULT '0',
  `count_walkers` tinyint NOT NULL DEFAULT '0',
  
#   for component-creator.com
  `ordering` int NOT NULL DEFAULT '0',
  `checked_out` int NULL DEFAULT '0',
  `checked_out_time` DATETIME NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `modified` DATETIME NULL DEFAULT NULL,
  `modified_by` int NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_group_code` (`group_code`),
    KEY `idx_contact_display_name` (`contact_display_name`),
    KEY `idx_walks_group_code` (`group_code`),
    KEY `idx_leader_user_id` (`leader_user_id`),
    KEY `idx_walk_date` (`walk_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
# ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__ra_walks_audit` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_amended` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `object_id` INT NOT NULL,
  `field_name` varchar(50) NOT NULL DEFAULT '',
  `record_type` char(1) NOT NULL DEFAULT '',
  `field_value` longtext,
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
# ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `#__ra_logfile`;
CREATE TABLE `#__ra_logfile` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `record_type` char(2) NOT NULL,
  `ref` varchar(10) DEFAULT NULL,
  `message` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
# ------------------------------------------------------------------------------