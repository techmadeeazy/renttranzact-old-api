<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 
 *
 * @author Temidayo Oluwabusola
 */
require APPPATH . '/libraries/REST_Controller.php';

class Payment extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        //$this->load->model('Base_model');
    }


    public function init_get($bookingId)
    {
        $this->load->model('InspectionBooking_model');

        $bookingData = $this->InspectionBooking_model->getById($bookingId);
        if (empty($bookingData)) {
            $this->response(['status' => 'fail', 'message' => 'Booking is not available']);
        }

        $this->response(['status' => 'success', 'data' => ['amount' => floatval($bookingData['agreed_amount']), 'agent_fee' => ($bookingData['agreed_amount'] * 0.1), 'legal_fee' => ($bookingData['agreed_amount'] * 0.1), 'caution_fee' => floatval($bookingData['caution_fee'])]]);
    }

    public function start_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $this->load->model('UserAuth_model');
        $userData = $this->UserAuth_model->getById($userAuthId);
        $bookingId = $this->post('booking_id');
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            $this->load->model('InspectionBooking_model');
            //get the booking data
            $bookingData = $this->InspectionBooking_model->getById($bookingId);
            if (empty($bookingData)) {
                $this->response(['status' => 'fail', 'message' => 'Booking is not available']);
            }

            if ($userAuthId == $bookingData['host_id']) {
                //approve with caution fee and agreed amount
                $this->response(['status' => 'fail', 'message' => 'You cannot pay for your own property']);
            }
            //start remita process
            $this->load->helper('string');
            $reference = random_string('md5');
            $totalAmount = floatval($bookingData['agreed_amount']) + floatval($bookingData['caution_fee']);
            $this->load->model('Payment_model');
            $processorReference = $this->getRemitaRRR($reference,$totalAmount); 
            $paymentData = ['reference' => $reference,'processor_reference' => $processorReference,'inspection_booking_id' => $bookingData['id'],'amount' => $totalAmount,'user_id' => $userAuthId,  ];
            $this->Payment_model->insertData($paymentData);

            $this->response(['status' => 'success', 'message' => 'Payment started', 'data' => $paymentData]);
        }
        $this->response(['status' => 'fail', 'message' => 'Please login']);
    }

    private function getRemitaRRR($orderId,$totalAmount,$otherData='')
    {
        $this->load->helper('string');
        return random_string('basic');
    }

    public function remita_rrr2_get()
    {
        $this->load->helper('string');
       // echo random_string('basic');
        $this->load->config('app');
        $orderId = md5(time());
        $totalAmount = 100;
        $apiKey = $this->config->item('remita_api_key');
        $apiHash = hash('sha512', $this->config->item('remita_merchant_id') . $this->config->item('remita_service_type_id')
            . $orderId . $totalAmount . $apiKey);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://remitademo.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
          "serviceTypeId": "' . $this->config->item('remita_service_type_id') . '",
          "amount": ' . $totalAmount . ',
          "orderId": "' . $orderId . '",
          "payerName": "Joe Olu",
          "payerEmail": "temidayo.joe@gmail.com",
          "payerPhone": "08034760836",
          "description": "Test payment"
      }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: remitaConsumerKey=' . $this->config->item('remita_merchant_id') . ',remitaConsumerToken=' . $apiHash . '',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
        $this->response(['status' => 'success','data' => ['api_key' => $apiKey,'orderId' => $orderId, 'amount' => $totalAmount,'api_hash' => $apiHash ],
        'service_type_id' => $this->config->item('remita_service_type_id'),
        'merchant_id' => $this->config->item('remita_merchant_id') , 'response' => json_decode($response,false)]);
    }

    public function remita_rrr_get()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://remitademo.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
          "serviceTypeId": "4430731",
          "amount": "100",
          "orderId": "' . md5(time()) . '",
          "payerName": "Joe Olu",
          "payerEmail": "temidayo.joe@gmail.com",
          "payerPhone": "08034760836",
          "description": "Test payment",
          "lineItems": [
              {
                  "lineItemsId": "username1",
                  "beneficiaryName": "Val Henry",
                  "beneficiaryAccount": "6020067886",
                  "bankCode": "058",
                  "beneficiaryAmount": "70",
                  "deductFeeFrom": "0"
              },
              {
                  "lineItemsId": "itemid2",
                  "beneficiaryName": "Sam Ola",
                  "beneficiaryAccount": "0360883515",
                  "bankCode": "058",
                  "beneficiaryAmount": "20",
                  "deductFeeFrom": "0"
              }
          ]
      }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: remitaConsumerKey=2547916,remitaConsumerToken=a3dd2e50d69f6e928d02d5a995aa059c80257edb530282f8ba27a2df6e39e8182e77aedb66f4e55771d5c6b67f426364e010e2dc970bfbef8b8e449164c67621',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
}
