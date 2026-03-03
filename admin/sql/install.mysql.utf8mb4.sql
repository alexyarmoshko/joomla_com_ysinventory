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

-- Root category node (required for nested-set operations)
INSERT INTO `#__ysi_categories` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `title`, `alias`, `published`, `access`, `language`)
VALUES (1, 0, 0, 1, 0, '', 'ROOT', 'root', 1, 1, '*')
ON DUPLICATE KEY UPDATE `id` = `id`;

--
-- Brands
--

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

--
-- Tag Groups
--

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

--
-- Tags
--

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
  KEY `idx_tag_alias` (`alias`(100)),
  CONSTRAINT `fk_ysi_tags_tag_group`
    FOREIGN KEY (`ysi_tag_group_id`)
    REFERENCES `#__ysi_tag_groups` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Items
--

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
  `ysi_status` tinyint NOT NULL DEFAULT 1 COMMENT '1=In Stock, 2=On Loan, 3=Maintenance, 4=Lost',
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

--
-- Item–Tag mapping (many-to-many)
--

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

--
-- Lends (lending workflow)
--

CREATE TABLE IF NOT EXISTS `#__ysi_lends` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ysi_item_id` int NOT NULL COMMENT 'FK to #__ysi_items',
  `ysi_user_id` int NOT NULL COMMENT 'FK to #__users (loanee)',
  `ysi_from` date NOT NULL,
  `ysi_to` date NOT NULL,
  `ysi_note` text,
  `ysi_status` tinyint NOT NULL DEFAULT 1 COMMENT '1=Requested, 2=On Loan, 3=Returned, 4=Lost, 5=Returned Damaged, 6=Returned Overdue',
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
