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

ALTER TABLE `user_reviews` 
ADD COLUMN `score` TINYINT(1) NULL AFTER `reviewed_id`,
CHANGE COLUMN `review` `score_description` VARCHAR(50) NULL DEFAULT NULL ;

ALTER TABLE `user_reviews` 
CHANGE COLUMN `score_description` `score_text` VARCHAR(50) NULL DEFAULT NULL ;

ALTER TABLE `property_reviews` 
CHANGE COLUMN `reviewed_id` `property_id` INT NOT NULL ;

ALTER TABLE `inspection_bookings` 
ADD COLUMN `payment_deadline` DATE NULL AFTER `agreed_amount`;

ALTER TABLE `user_profiles` 
DROP COLUMN `email_address`;

ALTER TABLE `user_auths` 
ADD COLUMN `blocked` TINYINT(1) NULL DEFAULT 0 AFTER `referral_code`;


ALTER TABLE `property_listings` 
CHANGE COLUMN `status` `status` ENUM('pending', 'approved') NULL DEFAULT 'pending' ;

ALTER TABLE `inspection_bookings` 
CHANGE COLUMN `status` `status` ENUM('vetting', 'pending', 'cancel', 'approve_payment', 'paid', 'complete') NULL DEFAULT 'vetting' ;

ALTER TABLE `user_auths` 
ADD COLUMN `deleted` INT UNSIGNED NULL AFTER `blocked`;

CREATE TABLE `user_wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_auth_id` int(11) NOT NULL,
  `available_amount` double NOT NULL,
  `ledger_amount` double NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_wallet_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_auth_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `note` varchar(100) DEFAULT NULL,
  `reference` varchar(50) NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `property_listings` 
ADD COLUMN `management_fee_percent` TINYINT(1) NULL DEFAULT 10 AFTER `display`;


ALTER TABLE `inspection_bookings` 
ADD COLUMN `legal_fee` DOUBLE NULL DEFAULT 0 AFTER `agreed_amount`,
ADD COLUMN `management_fee` DOUBLE NULL DEFAULT 0 AFTER `legal_fee`;


ALTER TABLE `inspection_bookings` 
ADD COLUMN `agent_fee` DOUBLE NULL DEFAULT 0 AFTER `management_fee`;

ALTER TABLE `inspection_bookings` 
ADD COLUMN `split_processed` TINYINT(1) NULL DEFAULT 0 COMMENT 'Has the split fee been processed? 1 = Yes, 0 = No' AFTER `modified`;

ALTER TABLE `user_wallets` 
ADD COLUMN `modified` DATETIME NULL DEFAULT NULL AFTER `created`;

ALTER TABLE `user_wallet_transactions` 
CHANGE COLUMN `reference` `reference` VARCHAR(50) NULL ;

CREATE TABLE `admin_earnings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) DEFAULT '0',
  `agent_fee` double DEFAULT '0',
  `legal_fee` double DEFAULT '0',
  `management_fee` double DEFAULT '0',
  `caution_fee` double DEFAULT '0',
  `total` double DEFAULT '0',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
