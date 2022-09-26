<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 
 *
 * @author Temidayo Oluwabusola
 */
require APPPATH . '/libraries/REST_Controller.php';

class Util extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        //$this->load->model('Base_model');
    }

    public function remita_token_get()
    {
        $this->response(['status' => 'success', 'data' => $this->getRemitaAccessToken()]);
    }

    public function bank_get($bankgId = '')
    {
        $this->load->model('Bank_model');
        $bankData = $this->Bank_model->getAll();
        $this->response(['status' => 'success', 'data' => $bankData]);
    }

    public function old_bank_account_enquiry_post()
    {
        $accountNumber = $this->post('account_number');
        $bankCode = $this->post('bank_code');

        $remitaAccessToken = $this->getRemitaAccessToken();
        log_message('debug', 'bank_account_enquiry: remita access token:' . $remitaAccessToken);
        $this->load->config('app');
        $remitaBaseURL = $this->config->item('remita_base_url');

        //echo 'toekn: '.$remitaAccessToken;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $remitaBaseURL . 'remita/exapp/api/v1/send/api/rpgsvc/v3/rpg/account/lookup',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ' {
    "sourceAccount": "' . $accountNumber . '",
    "sourceBankCode": "' . $bankCode . '"
 }
',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $remitaAccessToken
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $responseArray = json_decode($response, true);
        log_message('debug', 'bank_account_enquiry: response:' . $response);
        if (isset($responseArray['status']) && $responseArray['status'] == '00') {
            $this->response(['status' => 'success', 'data' => ['account_number' => $responseArray['data']['sourceAccount'], 'bank_code' => $responseArray['data']['sourceBankCode'], 'account_name' => $responseArray['data']['sourceAccountName']]]);
        }
        $this->response(['status' => 'fail', 'message' => $responseArray['message']]);
    }

    private function getRemitaAccessToken()
    {

        $this->load->helper('file');

        $fileInfo = get_file_info(APPPATH . 'config/remita_access_token.php');
        //Check if token was created within the last 1 hours. 3600 seconds is 1 hours.
        if ((time() - $fileInfo['date']) < 3600) {
            return file_get_contents(APPPATH . 'config/remita_access_token.php');
        }
        $this->load->config('app');
        $remitaBaseURL = $this->config->item('remita_base_url');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $remitaBaseURL . 'remita/exapp/api/v1/send/api/uaasvc/uaa/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
 "username": "' . $this->config->item('remita_public_key') . '",
 "password": "' . $this->config->item('remita_secret_key') . '"
}

',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        //echo $response;
        curl_close($curl);
        $responseArray = json_decode($response, true);
        if (isset($responseArray['data'][0]['accessToken'])) {
            //@TODO: log this
            $responseArray['access_token_status'] = write_file(APPPATH . 'config/remita_access_token.php', $responseArray['data'][0]['accessToken']);
            return $responseArray['data'][0]['accessToken'];
        }
        return null;
    }

    public  function i_bank_account_enquiry_post()
    {
        date_default_timezone_set('UTC');

        $accountNumber = $this->post('account_number');
        $bankCode = $this->post('bank_code');
        $timeStamp = date('c');
        $timeStamp = date('Y-m-d\TH:i:s+000000');
        $requestId = time();
        $apiHash = hash('sha512', $this->config->item('remita_api_key2') + $requestId + $this->config->item('remita_api_token'));

        $this->load->config('app');
        $remitaBaseURL = $this->config->item('remita_base_url');


        $curl = curl_init();

        $postData = '{
            "accountNo":"' . $this->encrypt($accountNumber, $this->config->item('remita_encrypt_vector'), $this->config->item('remita_encrypt_key')) . '",
            "bankCode":"' . $this->encrypt($bankCode, $this->config->item('remita_encrypt_vector'), $this->config->item('remita_encrypt_key')) . '"
         }';
        $headerData = array(
            'Content-Type: application/json',
            'MERCHANT_ID: ' . $this->config->item('remita_merchant_id'),
            'API_KEY: ' . $this->config->item('remita_api_key2'),
            'REQUEST_ID: ' . $requestId,
            'REQUEST_TS: ' . $timeStamp,
            'API_DETAILS_HASH: ' . $apiHash
        );

        log_message('debug', 'account_enquiry:body:' . $postData);
        log_message('debug', 'account_enquiry:header:' . json_encode($headerData));

        curl_setopt_array($curl, array(
            CURLOPT_URL => $remitaBaseURL . 'remita/exapp/api/v1/send/api/rpgsvc/rpg/api/v2/merc/fi/account/lookup',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headerData,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        //echo $response;
        $responseArray = json_decode($response, true);
        log_message('debug', 'bank_account_enquiry: response:' . $response);
        if (isset($responseArray['status']) && $responseArray['status'] == 'success' && $responseArray['data']['responseCode'] == '00') {
            $this->response(['status' => 'success', 'data' => ['account_number' => $responseArray['data']['accountNo'], 'bank_code' => $responseArray['data']['044'], 'account_name' => $responseArray['data']['accountName']]]);
        }
        $this->response(['status' => 'fail', 'message' => $responseArray['message']]);
    }

    public  function bank_account_enquiry_post()
    {

        $accountNumber = $this->post('account_number');
        $bankCode = $this->post('bank_code');
        //log_message('debug', 'account_enquiry:body:' . $postData);
        //log_message('debug', 'account_enquiry:header:' . json_encode($headerData));
        $accountName =  $this->flwVerifyBankAccountName($bankCode, $accountNumber);

        log_message('debug', 'bank_account_enquiry: response:' . $accountName);
        if (empty($accountName)) {
            $this->response(['status' => 'fail', 'message' => 'Name enquiry failed']);
        }

        $this->response(['status' => 'success', 'data' => ['account_number' => $accountNumber, 'bank_code' => $bankCode, 'account_name' => $accountName]]);
    }

    private    function encrypt($data, $iv, $key)
    {
        $cipherText = trim(base64_encode(openssl_encrypt($data, 'AES-128-CBC', $key, true, $iv)));
        unset($data, $iv, $key);
        return $cipherText;
    }

    public function flwVerifyBankAccountName($bankCode, $bankAccountNumber)
    {
        $this->load->config('app');
        $pageData['flw_public_key'] = $this->config->item('flw_sec_key');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.flutterwave.com/v3/accounts/resolve',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "account_number": "' . $bankAccountNumber . '",
            "account_bank": "' . $bankCode . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->config->item('flw_sec_key'),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        //echo $response;
        //exit;
        $responseArray = json_decode($response, TRUE);
        if (isset($responseArray['status']) &&  ($responseArray['status'] == 'success')) {
            return $responseArray['data']['account_name'];
        }
        return null;
    }

    public function config_get()
    {
        $config = ['RT_email' => 'customersupport@rentranzact.com', 'RT_phone' => '+23418880440', 'RT_twitter' => 'https://twitter.com/Rentranzact', 'RT_facebook' => 'https://www.facebook.com/Rentranzact-111072748262817/', 'RT_instagram' => 'https://www.instagram.com/rentranzact/?hl=en', 'RT_whatsapp' => '+2349169582742'];
        $this->response(['status' => 'success', 'data' => $config]);
    }
}
