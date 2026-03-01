-- Yak Shaver Inventory — 1.0.3 update: add FK constraint on tags → tag groups

ALTER TABLE `#__ysi_tags`
  ADD CONSTRAINT `fk_ysi_tags_tag_group`
    FOREIGN KEY (`ysi_tag_group_id`)
    REFERENCES `#__ysi_tag_groups` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;
