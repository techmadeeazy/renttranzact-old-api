ALTER TABLE `property_listings` CHANGE COLUMN `asking_price` `asking_price` DECIMAL(10,0) NULL

ALTER TABLE `rtrans_staging`.`user_profiles` 
ADD COLUMN `address` VARCHAR(100) NULL AFTER `primary_role`,
ADD COLUMN `resident_state` VARCHAR(45) NULL AFTER `address`,
ADD COLUMN `resident_lga` VARCHAR(45) NULL AFTER `resident_state`;


ALTER TABLE `rtrans_staging`.`payment_transactions` 
ADD COLUMN `payer_name` VARCHAR(100) NULL AFTER `user_id`,
ADD COLUMN `payer_email` VARCHAR(50) NULL AFTER `payer_name`,
ADD COLUMN `payer_phone` VARCHAR(25) NULL AFTER `payer_email`,
ADD COLUMN `description` TEXT NULL AFTER `payer_phone`,
ADD COLUMN `reference` VARCHAR(45) NULL AFTER `description`,
ADD COLUMN `processor_reference` VARCHAR(45) NULL AFTER `reference`;

ALTER TABLE `rtrans_staging`.`payment_transactions` 
ADD COLUMN `inspection_booking_id` INT NULL AFTER `processor_reference`;


ALTER TABLE `user_profiles` ADD `bank_code` VARCHAR(5) NULL AFTER `rc_number`, ADD `bank_account_number` VARCHAR(10) NULL AFTER `bank_code`, ADD `bank_account_name` VARCHAR(100) NULL AFTER `bank_account_number`;

ALTER TABLE `payment_transactions`  ADD `payment_status` ENUM('pending','successful','failed') NOT NULL DEFAULT 'pending'  AFTER `inspection_booking_id`;

ALTER TABLE `user_profiles` 
ADD COLUMN `company_name` VARCHAR(45) NULL AFTER `bank_account_name`;

ALTER TABLE `user_auths` 
ADD COLUMN `email_address` VARCHAR(100) NOT NULL AFTER `username`;

ALTER TABLE `inspection_bookings` 
ADD COLUMN `start_date` DATE NULL AFTER `agreed_amount`,
ADD COLUMN `end_date` DATE NULL AFTER `start_date`,
CHANGE COLUMN `status` `status` ENUM('pending', 'cancel', 'approve_payment', 'paid', 'complete') NULL DEFAULT 'pending' AFTER `end_date`;

ALTER TABLE `property_listings` 
CHANGE COLUMN `purpose` `purpose` ENUM('Residential', 'Commercial') NULL DEFAULT NULL ;

ALTER TABLE `user_auths` 
ADD COLUMN `email_verified` TINYINT(1) NULL AFTER `email_address`;

ALTER TABLE `preminders` 
RENAME TO  `user_password_resets` ;
