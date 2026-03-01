-- Yak Shaver Inventory 1.0.0

CREATE TABLE IF NOT EXISTS `#__ysi_inventories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` mediumtext,
  `ysi_contact_user_id` int NOT NULL DEFAULT 0 COMMENT 'Joomla user ID reference (XOR with ysi_contact_contact_id)',
  `ysi_contact_contact_id` int NOT NULL DEFAULT 0 COMMENT 'Joomla contact ID reference (XOR with ysi_contact_user_id)',
  `ysi_contact_id` int NOT NULL DEFAULT 0 COMMENT 'Deprecated legacy user ID reference',
  `published` tinyint NOT NULL DEFAULT 0,
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_contact_user` (`ysi_contact_user_id`),
  KEY `idx_contact_contact` (`ysi_contact_contact_id`),
  KEY `idx_contact_legacy` (`ysi_contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Categories (component-local, Joomla nested-set structure)
--

CREATE TABLE IF NOT EXISTS `#__ysi_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'FK to #__assets for ACL',
  `parent_id` int unsigned NOT NULL DEFAULT 0,
  `lft` int NOT NULL DEFAULT 0,
  `rgt` int NOT NULL DEFAULT 0,
  `level` int unsigned NOT NULL DEFAULT 0,
  `path` varchar(400) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` mediumtext,
  `published` tinyint NOT NULL DEFAULT 0,
  `access` int unsigned NOT NULL DEFAULT 1,
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `params` text,
  `metadesc` varchar(1024) NOT NULL DEFAULT '',
  `metakey` varchar(1024) NOT NULL DEFAULT '',
  `created_user_id` int unsigned NOT NULL DEFAULT 0,
  `created_time` datetime,
  `modified_user_id` int unsigned NOT NULL DEFAULT 0,
  `modified_time` datetime,
  `language` char(7) NOT NULL DEFAULT '*',
  `version` int unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_cat_parent` (`parent_id`),
  KEY `idx_cat_published` (`published`),
  KEY `idx_cat_access` (`access`),
  KEY `idx_cat_checkout` (`checked_out`),
  KEY `idx_cat_lft_rgt` (`lft`, `rgt`),
  KEY `idx_cat_alias` (`alias`(100)),
  KEY `idx_cat_path` (`path`(100)),
  KEY `idx_cat_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__ysi_categories` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `title`, `alias`, `published`, `access`, `language`)
VALUES (1, 0, 0, 1, 0, '', 'ROOT', 'root', 1, 1, '*')
ON DUPLICATE KEY UPDATE `id` = `id`;
