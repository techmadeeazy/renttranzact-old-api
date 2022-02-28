<?php
/**
 * Production Configuration for Paystack controller
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$config['paystack_mode'] = 'LIVE';
$config['paystack_api_key'] = '';
$config['paystack_split_code'] = 'SPL_7Olt7agNJ9';
$config['file_location'] = "/var/www/html/api_hub/public/download/";
$config['create_policy_url'] = "https://api.coronationinsurance.com.ng/api/CreatePolicy";
$config['policy_creation_token'] = '7BD6EDFF789A174B51797AA780DF2E6DC450';