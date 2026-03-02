-- Phase 7: Lends (lending workflow)

CREATE TABLE IF NOT EXISTS `#__ysi_lends` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ysi_item_id` int NOT NULL COMMENT 'FK to #__ysi_items',
  `ysi_user_id` int NOT NULL COMMENT 'FK to #__users (borrower)',
  `ysi_from` date NOT NULL,
  `ysi_to` date NOT NULL,
  `ysi_note` text,
  `ysi_status` tinyint NOT NULL DEFAULT 1 COMMENT '1=Requested, 2=Borrowed, 3=Returned, 4=Lost',
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_lend_item` (`ysi_item_id`),
  KEY `idx_lend_user` (`ysi_user_id`),
  KEY `idx_lend_status` (`ysi_status`),
  CONSTRAINT `fk_ysi_lends_item`
    FOREIGN KEY (`ysi_item_id`)
    REFERENCES `#__ysi_items` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
