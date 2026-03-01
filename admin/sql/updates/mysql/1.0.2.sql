-- Yak Shaver Inventory — 1.0.2 update: tag groups and tags

CREATE TABLE IF NOT EXISTS `#__ysi_tag_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` mediumtext,
  `published` tinyint NOT NULL DEFAULT 0,
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_taggroup_published` (`published`),
  KEY `idx_taggroup_checkout` (`checked_out`),
  KEY `idx_taggroup_alias` (`alias`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ysi_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ysi_tag_group_id` int NOT NULL DEFAULT 0 COMMENT 'FK to #__ysi_tag_groups',
  `name` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` mediumtext,
  `published` tinyint NOT NULL DEFAULT 0,
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tag_group` (`ysi_tag_group_id`),
  KEY `idx_tag_published` (`published`),
  KEY `idx_tag_checkout` (`checked_out`),
  KEY `idx_tag_alias` (`alias`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
