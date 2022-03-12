ALTER TABLE `property_listings` CHANGE COLUMN `asking_price` `asking_price` DECIMAL(10,0) NULL

ALTER TABLE `rtrans_staging`.`user_profiles` 
ADD COLUMN `address` VARCHAR(100) NULL AFTER `primary_role`,
ADD COLUMN `resident_state` VARCHAR(45) NULL AFTER `address`,
ADD COLUMN `resident_lga` VARCHAR(45) NULL AFTER `resident_state`;