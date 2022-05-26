<?php
/**
 * Configuration for the  Paystack controller
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$config['paystack_mode'] = 'TEST';
$config['paystack_api_key'] = 'sk_test_ae37b3436231122942d63f5493b372daabf4c631';
$config['paystack_split_code'] = 'SPL_7Olt7agNJ9';
$config['file_location'] = "C:\laragon\www\api_hub\public\download\\";
$config['create_policy_url'] = "http://staging-api.coronationinsurance.com.ng/api/CreatePolicy";
$config['policy_creation_token'] = 'C1661EB17379D74C4A7B7E973268F9BAC276';
$config['generate_policy_doc_url'] = 'http://staging-api.coronationinsurance.com.ng/api/Policy/';
