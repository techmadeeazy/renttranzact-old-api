ALTER TABLE `home_insurance_quotes` ADD `customer_title` VARCHAR(10) NULL AFTER `id`;

ALTER TABLE `life_insurance_quotes` ADD `occupation` VARCHAR(100) NULL AFTER `gender`;

ALTER TABLE `life_insurance_quotes` ADD `address` VARCHAR(150) NULL AFTER `phone`, ADD `beneficiary_first_name` VARCHAR(25) NULL AFTER `address`, ADD `beneficiary_last_name` VARCHAR(25) NULL AFTER `beneficiary_first_name`, ADD `beneficiary_relationship_status` VARCHAR(25) NULL AFTER `beneficiary_last_name`, ADD `beneficiary_address` VARCHAR(200) NULL AFTER `beneficiary_relationship_status`, ADD `contingent_first_name` VARCHAR(25) NULL AFTER `beneficiary_address`, ADD `contingent_last_name` VARCHAR(25) NULL AFTER `contingent_first_name`, ADD `contingent_relationship_status` VARCHAR(25) NULL AFTER `contingent_last_name`, ADD `contingent_address` VARCHAR(200) NULL AFTER `contingent_relationship_status`;

ALTER TABLE `life_insurance_quotes` ADD `beneficiary_dob` DATE NULL AFTER `beneficiary_address`;

ALTER TABLE `life_insurance_quotes` ADD `contingent_dob` DATE NULL AFTER `contingent_address`;

ALTER TABLE `contactus` CHANGE `contact_email` `contact_email` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `contactus` CHANGE `contact_owner` `contact_owner` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;