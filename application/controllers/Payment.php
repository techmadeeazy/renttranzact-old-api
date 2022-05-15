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
        $userData['profile']['email_address'] = $userData['email_address'];

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
            //$processorReference = $this->getRemitaRRR($reference, $totalAmount, $userData['profile']);
            $processorReference = $this->getSplitRemitaRRR($reference, $totalAmount, $userData['profile']);
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

        $postData = '{
                "serviceTypeId": "' . $this->config->item('remita_service_type_id') . '",
                "amount": ' . $totalAmount . ',
                "orderId": "' . $orderId . '",
                "payerName": "' . $otherData['first_name'] . ' ' . $otherData['last_name'] . '",
                "payerEmail": "' . $otherData['email_address'] . '",
                "payerPhone": "' . $otherData['phone'] . '",
                "description": "Payment to RentTranzact"
            }';
        log_message('debug', 'getRemitaRRR:postData:' . $postData);
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
            CURLOPT_POSTFIELDS => $postData,
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

    private  function getSplitRemitaRRR($orderId, $totalAmount, $otherData = '')
    {
        $this->load->config('app');
        $orderId = md5(time());
        $apiKey = $this->config->item('remita_api_key');
        $apiHash = hash('sha512', $this->config->item('remita_merchant_id') . $this->config->item('remita_service_type_id')
            . $orderId . $totalAmount . $apiKey);

        $cautionFee = 0.1 * $totalAmount;
        $legalFee = 0.1 *  $totalAmount;
        $agencyFee = 0.1 * $totalAmount;

        $splitAccount = '"lineItems":[
            {
               "lineItemsId":"CAUTION FEE",
               "beneficiaryName":"RENT TRANZACT LTD",
               "beneficiaryAccount":"0088230570",
               "bankCode":"058",
               "beneficiaryAmount":"'.$cautionFee.'",
               "deductFeeFrom":"1"
            },
            {
               "lineItemsId":"REFERRAL/AGENT FEE",
               "beneficiaryName":"RENT TRANZACT LTD",
               "beneficiaryAccount":"0360883515",
               "bankCode":"058",
               "beneficiaryAmount":"'.$agencyFee.'",
               "deductFeeFrom":"0"
            },{
                "lineItemsId":"LEGAL FEE",
                "beneficiaryName":"RENT TRANZACT LTD",
                "beneficiaryAccount":"0088617010",
                "bankCode":"058",
                "beneficiaryAmount":"'.$legalFee.'",
                "deductFeeFrom":"0"
             }
         ]';

        $postData = '{
                "serviceTypeId": "' . $this->config->item('remita_service_type_id') . '",
                "amount": ' . $totalAmount . ',
                "orderId": "' . $orderId . '",
                "payerName": "' . $otherData['first_name'] . ' ' . $otherData['last_name'] . '",
                "payerEmail": "' . $otherData['email_address'] . '",
                "payerPhone": "' . $otherData['phone'] . '",
                "description": "Payment to RentTranzact",
                ' . $splitAccount . '
            }';

        log_message('debug', 'getRemitaRRR:postData:' . $postData);
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
            CURLOPT_POSTFIELDS => $postData,
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
        $apiHash = hash('sha512', $rrr . $apiKey . $this->config->item('remita_merchant_id'));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://remitademo.net/remita/exapp/api/v1/send/api/echannelsvc/' . $this->config->item('remita_merchant_id') . '/' . $rrr . '/' . $apiHash . '/status.reg',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: remitaConsumerKey=' . $this->config->item('remita_merchant_id') . ',remitaConsumerToken=' . $apiHash . ''
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $responseArray = json_decode($response, true);
        $status = 'pending';
        if (isset($responseArray['status'])) {

            switch ($responseArray['status']) {
                case '00':
                    $status = 'paid';
                    $this->updatePaymentStatus($rrr);
                    break;
                case '021':
                    $status = 'pending';
                    break;
            }

            //  {"amount":100.0,"RRR":"130008289359","orderId":"362ab503bbe5d8a405894f531135eb1d","message":"Successful","paymentDate":"2022-04-18
            //    01:54:52 PM","transactiontime":"2022-04-18 12:00:00 AM","status":"00"}
            $this->response(['status' => 'success', 'data' => ['status' => $status, 'amount' => $responseArray['amount'], 'RRR' => $rrr]]);
        }
        //echo $response;
        $this->response(['status' => 'fail', 'message' => 'Status cannot be retrieved', 'debug' => $response]);
    }

    private function updatePaymentStatus($processorReference)
    {
        $this->load->model('InspectionBooking_model');
        $this->load->model('Payment_model');
        $paymentData = $this->Payment_model->getBy($processorReference, 'processor_reference');
        if (!empty($paymentData)) {
            //update
            //updateById($data, $id)
            $this->Payment_model->updateById(['payment_status' => 'successful'], $paymentData['id']);
            //update inspection booking id
            $this->InspectionBooking_model->updateById(['status' => 'paid'], $paymentData['inspection_booking_id']);
        }
    }

    public function create_mandate_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $monthlyAmount = $this->post('amount');

        $this->load->config('app');
        $requestId = random_string('md5');
        $apiKey = $this->config->item('remita_api_key');
        //merchantId+serviceTypeId+requestId+amt+api_key
        $apiHash = hash('sha512', $this->config->item('remita_merchant_id') . $this->config->item('remita_service_type_id')
            . $requestId . $monthlyAmount . $apiKey);

        $this->load->model('UserAuth_model');
        $this->load->model('UserProfile_model');

        $userData = $this->UserAuth_model->getById($userAuthId);
        $userData['profile'] = $this->UserProfile_model->getBy($userData['id'], 'user_auth_id');
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            //start remita mandate process
            $this->load->helper('string');
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://remitademo.net/remita/exapp/api/v1/send/api/echannelsvc/echannel/mandate/setup',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
      "merchantId":"' . $this->config->item('remita_merchant_id') . '",
      "serviceTypeId":"' . $this->config->item('remita_service_type_id') . '",
      "requestId":"' . $requestId . '",
      "hash":"' . $apiHash . '",
      "payerName":"Joe Olu",
      "payerEmail":"temidayo.joe@gmail.com",
      "payerPhone":"08034760836",
      "payerBankCode":"044",
      "payerAccount":"4589999044",
      "amount":"' . $monthlyAmount . '",
      "startDate":"",
      "endDate":"",
      "mandateType":"DD",
      "maxNoOfDebits": "3"
}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;
        }
    }
    private function createMandateSchedule()
    {
        $date = new DateTime('31-08-2015');

        function getCutoffDate($date)
        {
            $days = cal_days_in_month(CAL_GREGORIAN, $date->format('n'), $date->format('Y'));
            $date->add(new DateInterval('P' . $days . 'D'));
            return $date;
        }

        for ($i = 0; $i < 5; $i++) {
            $date = getCutoffDate($date);
            echo $date->format('d-m-Y') . '<br>';
        }
    }

    /* public function history_get($userAuthId)
    {
        $this->load->model('Base_model');
        $result = $this->Base_model->get_many('payment_transactions', ['user_id' => $userAuthId]);
        $this->response(["status" => "success", "data" => $result]);
    }
    */

    public function history_get($userAuthId, $role = '')
    {
        $this->load->model('UserAuth_model');
        $this->load->model('InspectionBooking_model');
        $this->load->model('Base_model');
        //$userAuthData =  $this->UserAuth_model->getById($userAuthId);
        $this->load->model('UserProfile_model');

        $bookingResponse = [];
        switch ($role) {
            case 'agent':
                $bookingData = $this->Base_model->get_many('inspection_bookings', ['host_id' => $userAuthId, 'status' => 'paid']);
                //$bookingData = $this->InspectionBooking_model->getAllBy($userAuthId, 'host_id');
                foreach ($bookingData as $b) {

                    $inspectorData = $this->UserProfile_model->getBy($b['inspector_id'], 'user_auth_id');
                    unset($inspectorData['address'], $inspectorData['created'], $inspectorData['modified'], $inspectorData['status'], $inspectorData['primary_role'], $inspectorData['id'], $inspectorData['lga'], $inspectorData['rc_number']);
                    $b['inspector'] = $inspectorData;
                    $bookingResponse[] = $b;
                }
                break;
            case 'tenant':
                $this->load->model('Property_model');
                $this->load->model('PropertyImage_model');
                $bookingData = $this->Base_model->get_many('inspection_bookings', ['inspector_id' => $userAuthId, 'status' => 'paid']);
                //$bookingData = $this->InspectionBooking_model->getAllBy($userAuthId, 'inspector_id');
                foreach ($bookingData as $b) {
                    $propertyData = $this->Property_model->getById($b['property_id']);
                    $imageData = $this->PropertyImage_model->getFeaturedImage($b['property_id']);
                    if (empty($imageData)) {
                        //set default values
                        $propertyData['image_url'] = 'https://res.cloudinary.com/rent-tranzact-limited/image/upload/v1647366660/bkfn512urnate2dlmxge.jpg';
                        $propertyData['image_title'] = '';
                    } else {
                        $propertyData['image_url'] = $imageData['url'];
                        $propertyData['image_title'] = $imageData['title'];
                    }

                    $hostData = $this->UserProfile_model->getBy($b['host_id'], 'user_auth_id');
                    unset($hostData['address'], $hostData['created'], $hostData['modified'], $hostData['status'], $hostData['primary_role'], $hostData['id'], $hostData['lga'], $hostData['rc_number']);
                    $b['host'] = $hostData;
                    $b['property'] = $propertyData;
                    $bookingResponse[] = $b;
                }
                break;
            default:
                $bookingData = $this->Base_model->get_many('inspection_bookings', "(inspector_id = $userAuthId OR host_id = $userAuthId ) AND 'status' = 'paid'");
                foreach ($bookingData as $b) {
                    $bookingResponse[] = $b;
                }
                break;
        }
        $this->response(['status' => 'success', 'data' => $bookingResponse]);
    }
}
