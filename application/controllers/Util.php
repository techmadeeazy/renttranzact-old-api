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
                'Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJtUC05LVFXRmczVWMxTjNQZjE0SnFYZldSc2lCUl9DcnA5LWtjVjE0cFdjIn0.eyJleHAiOjE2NTAwMTIxNjUsImlhdCI6MTY1MDAwODU2NSwianRpIjoiMDhhNTM0MDItNmJhMC00NDEwLTg5NzQtM2QxZjdiMWQ4MDViIiwiaXNzIjoiaHR0cDovLzEwLjEuMS44Mjo5MTgwL2tleWNsb2FrL3JlbWl0YS9leGFwcC9hcGkvdjEvcmVkZ2F0ZS9hdXRoL3JlYWxtcy9yZW1pdGEiLCJhdWQiOlsiZGlzY292ZXJ5LXNlcnZlciIsImFjY291bnQiXSwic3ViIjoiZjU0ODNmMTYtNTQzYS00ZjI1LWJjMTYtNGNkMzZjMjBlNTlhIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoicmVtaXRhdWFhLXNlcnZpY2UiLCJzZXNzaW9uX3N0YXRlIjoiODgyOTY4ODUtNzRiYS00Y2YwLWJkNjYtZTk3MjQ4ZDI0N2M3IiwiYWNyIjoiMSIsImFsbG93ZWQtb3JpZ2lucyI6WyJodHRwczovL2xvZ2luLnJlbWl0YS5uZXQiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iXX0sInJlc291cmNlX2FjY2VzcyI6eyJkaXNjb3Zlcnktc2VydmVyIjp7InJvbGVzIjpbIm1hbmFnZS1hY2NvdW50Iiwidmlldy1wcm9maWxlIl19LCJhY2NvdW50Ijp7InJvbGVzIjpbIm1hbmFnZS1hY2NvdW50IiwibWFuYWdlLWFjY291bnQtbGlua3MiLCJ2aWV3LXByb2ZpbGUiXX19LCJzY29wZSI6ImVtYWlsIHByb2ZpbGUiLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwibmFtZSI6IkFERUJBWU8gQURFQkFZTyIsInByZWZlcnJlZF91c2VybmFtZSI6InVoc3U2emltYXZ4bnpoeHciLCJnaXZlbl9uYW1lIjoiQURFQkFZTyIsImZhbWlseV9uYW1lIjoiQURFQkFZTyIsIm9yZ2FuaXNhdGlvbi1pZCI6IkNXR0RFTU8iLCJlbWFpbCI6IjAxMiJ9.ACvhRfPFoFIk4vbJuYaC3WUCfXY_yogb4ChvFly4S6LLoeHLDRiKB0TXFovVdVCJKotvb_A4PBrW5naAnkPnhCGPtvi4osrZQWu6N_KARlsr9pE_MSf9z3qL-DfEGFcyVdxn6h8JUu6Tnrj9B75WhYiZeB0hpWleHpbHt-ysY_0ND0VVuWBD8NDlPvmaiLEkgZicPd3P5HfSez5qiPKJiYXqAutFLgAVHo8xgOeZDI-8m8_fPLNb3I6KafksgdxJw7ufnw0yhgBgSvmvDxY-WnAKZSaKzr7MK_yLhiCKtO0Bbq3mrir9ReY6D-EUfZAxY88PXngwfFN6nFSmoWmy1g',
                'Cookie: JSESSIONID=AVKyxaXKtV3_DXcda1qzxwsE.9eb07e7cd645'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
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

        curl_close($curl);
        $responseArray = json_decode($response, true);
        if (isset($responseArray['data']['access_token'])) {
            //@TODO: log this
            $responseArray['access_token_status'] = write_file(APPPATH . 'config/remita_access_token.php', $responseArray['data']['access_token']);
            return $responseArray['data']['access_token'];
        }
        return null;
    }
}
