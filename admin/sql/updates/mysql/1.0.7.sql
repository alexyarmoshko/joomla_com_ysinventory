-- Yak Shaver Inventory — 1.0.7 update SQL

ALTER TABLE `#__ysi_items` ADD COLUMN `ysi_status` tinyint NOT NULL DEFAULT 1 COMMENT '1=In Stock, 2=On Loan, 3=Maintenance, 4=Lost' AFTER `ysi_quantity`;

-- `#__ysi_lends`.`ysi_status` enum meaning changed (1=Requested, 2=On Loan, 3=Returned, 4=Lost, 5=Returned Damaged, 6=Returned Overdue). No data migration needed.
