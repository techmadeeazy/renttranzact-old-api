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

    public function bank_account_enquiry_post()
    {
        $accountNumber = $this->post('account_number');
        $bankCode = $this->post('bank_code');

        $remitaAccessToken = $this->getRemitaAccessToken();
        //echo 'toekn: '.$remitaAccessToken;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://remitademo.net/remita/exapp/api/v1/send/api/rpgsvc/v3/rpg/account/lookup',
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

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://remitademo.net/remita/exapp/api/v1/send/api/uaasvc/uaa/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
 "username": "UHSU6ZIMAVXNZHXW",
 "password": "K8JE73OFE508GMOW9VWLX5SLH5QG1PF2"
}

',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: JSESSIONID=AVKyxaXKtV3_DXcda1qzxwsE.9eb07e7cd645'
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
}
