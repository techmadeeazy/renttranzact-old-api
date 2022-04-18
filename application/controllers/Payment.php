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
        $this->load->model('UserProfile_model');

        $userData = $this->UserAuth_model->getById($userAuthId);
        $userData['profile'] = $this->UserProfile_model->getBy($userData['id'], 'user_auth_id');
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
            $processorReference = $this->getRemitaRRR($reference, $totalAmount, $userData['profile']);
            if (empty($processorReference)) {
                $this->response(['status' => 'fail', 'message' => 'Reference(RRR) cannot be generated']);
            }
            $paymentData = ['reference' => $reference, 'processor_reference' => $processorReference, 'inspection_booking_id' => $bookingData['id'], 'amount' => $totalAmount, 'user_id' => $userAuthId,];
            $this->Payment_model->insertData($paymentData);
            $paymentData['payment_url'] = 'https://remitademo.net/remita/onepage/biller/' . $processorReference . '/payment.spa';
            $this->response(['status' => 'success', 'message' => 'Payment started', 'data' => $paymentData]);
        }
        $this->response(['status' => 'fail', 'message' => 'Please login']);
    }

    private function getRemitaRRR($orderId, $totalAmount, $otherData = '')
    {
        $this->load->config('app');
        $orderId = md5(time());
        $apiKey = $this->config->item('remita_api_key');
        $apiHash = hash('sha512', $this->config->item('remita_merchant_id') . $this->config->item('remita_service_type_id')
            . $orderId . $totalAmount . $apiKey);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->config->item('remita_pay_url'),
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
              "payerName": "' . $otherData['first_name'] . ' ' . $otherData['last_name'] . '",
              "payerEmail": "' . $otherData['email_address'] . '",
              "payerPhone": "' . $otherData['phone'] . '",
              "description": "Payment to RentTranzact"
          }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: remitaConsumerKey=' . $this->config->item('remita_merchant_id') . ',remitaConsumerToken=' . $apiHash . '',
                'Content-Type: application/json'
            ),
        ));
        $jsopResponse = curl_exec($curl);
        log_message('debug', 'getRemitaRRR:' . $jsopResponse);
        curl_close($curl);
        $response = json_decode(trim($jsopResponse, "jsonp ( )"), true);
        if (isset($response['statuscode']) && $response['statuscode'] == '025') {
            return $response['RRR'];
        }
        return null;
    }

    public function remita_rrr_get()
    {
        $this->load->helper('string');
        // echo random_string('basic');
        $this->load->config('app');
        $orderId = md5(time());
        $totalAmount = 100;
        $otherData = ['first_name' => 'Joe', 'last_name' => 'Olu', 'email_address' => 'temidayo@expertfingers.com', 'phone' => '08034760836'];
        $this->response([
            'status' => 'success', 'data' => ['RRR' => $this->getRemitaRRR($orderId, $totalAmount, $otherData)]
        ]);
    }

    public function status_get($rrr)
    {
        $this->load->config('app');
        $apiKey = $this->config->item('remita_api_key');
        $apiHash = hash('sha512', $rrr.$apiKey.$this->config->item('remita_merchant_id'));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://remitademo.net/remita/exapp/api/v1/send/api/echannelsvc/'.$this->config->item('remita_merchant_id').'/'.$rrr.'/'.$apiHash.'/status.reg',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: remitaConsumerKey='.$this->config->item('remita_merchant_id').',remitaConsumerToken='.$apiHash.''
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
}
