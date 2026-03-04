-- Yak Shaver Inventory 1.0.8 — Lends journal log table

CREATE TABLE IF NOT EXISTS `#__ysi_lends_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'Joomla user ID of actor performing the operation',
  `log_date` datetime NOT NULL COMMENT 'Timestamp of logging action',
  `operation` char(1) NOT NULL COMMENT 'U=Update, D=Delete',
  `message` text NOT NULL COMMENT 'JSON payload: lend_id, snapshot, and human-readable details',
  PRIMARY KEY (`id`),
  KEY `idx_lendlog_user` (`user_id`),
  KEY `idx_lendlog_date` (`log_date`),
  KEY `idx_lendlog_operation` (`operation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
