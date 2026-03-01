-- Phase 6: Items and item–tag mapping

CREATE TABLE IF NOT EXISTS `#__ysi_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` mediumtext,
  `ysi_inventory_id` int NOT NULL DEFAULT 0 COMMENT 'FK to #__ysi_inventories',
  `catid` int NOT NULL DEFAULT 0 COMMENT 'FK to #__ysi_categories',
  `brand_id` int NOT NULL DEFAULT 0 COMMENT 'FK to #__ysi_brands',
  `ysi_location_user_id` int NOT NULL DEFAULT 0 COMMENT 'FK to #__users (Location)',
  `image` varchar(1024) NOT NULL DEFAULT '',
  `ysi_model` varchar(255) NOT NULL DEFAULT '',
  `ysi_serial_number` varchar(255) NOT NULL DEFAULT '',
  `ysi_sku` varchar(255) NOT NULL DEFAULT '',
  `ysi_quantity` int NOT NULL DEFAULT 1,
  `published` tinyint NOT NULL DEFAULT 0,
  `access` int unsigned NOT NULL DEFAULT 1,
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_item_published` (`published`),
  KEY `idx_item_access` (`access`),
  KEY `idx_item_checkout` (`checked_out`),
  KEY `idx_item_alias` (`alias`(100)),
  KEY `idx_item_inventory` (`ysi_inventory_id`),
  KEY `idx_item_catid` (`catid`),
  KEY `idx_item_brand` (`brand_id`),
  KEY `idx_item_location` (`ysi_location_user_id`),
  CONSTRAINT `fk_ysi_items_inventory`
    FOREIGN KEY (`ysi_inventory_id`)
    REFERENCES `#__ysi_inventories` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ysi_items_category`
    FOREIGN KEY (`catid`)
    REFERENCES `#__ysi_categories` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ysi_items_brand`
    FOREIGN KEY (`brand_id`)
    REFERENCES `#__ysi_brands` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ysi_item_tag_map` (
  `ysi_item_id` int NOT NULL COMMENT 'FK to #__ysi_items',
  `ysi_tag_id` int NOT NULL COMMENT 'FK to #__ysi_tags',
  PRIMARY KEY (`ysi_item_id`, `ysi_tag_id`),
  KEY `idx_item_tag_tag` (`ysi_tag_id`),
  CONSTRAINT `fk_ysi_itm_item`
    FOREIGN KEY (`ysi_item_id`)
    REFERENCES `#__ysi_items` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ysi_itm_tag`
    FOREIGN KEY (`ysi_tag_id`)
    REFERENCES `#__ysi_tags` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
