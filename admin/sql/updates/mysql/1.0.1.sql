-- Yak Shaver Inventory — 1.0.1 update: add brands and locations tables

CREATE TABLE IF NOT EXISTS `#__ysi_brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` mediumtext,
  `image` varchar(1024) NOT NULL DEFAULT '',
  `published` tinyint NOT NULL DEFAULT 0,
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_brand_published` (`published`),
  KEY `idx_brand_checkout` (`checked_out`),
  KEY `idx_brand_alias` (`alias`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ysi_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `ysi_contact_user_id` int NOT NULL DEFAULT 0 COMMENT 'Joomla user ID reference (required)',
  `published` tinyint NOT NULL DEFAULT 0,
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_location_published` (`published`),
  KEY `idx_location_checkout` (`checked_out`),
  KEY `idx_location_contact_user` (`ysi_contact_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
