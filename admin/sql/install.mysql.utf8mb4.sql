-- Yak Shaver Inventory — install SQL

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
