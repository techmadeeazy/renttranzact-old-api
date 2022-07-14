<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Bg extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function update_status()
    {
        $this->load->config('app');
        $apiKey = $this->config->item('remita_api_key');
        $this->load->model('Payment_model');

        //get pending payments
        $pendingPayments = $this->Payment_model->getPendingPayments();

        if (empty($pendingPayments)) {
            exit('No pending payments');
        }
        $remitaBaseURL = $this->config->item('remita_base_url');
        foreach ($pendingPayments as $pp) {
            $rrr = $pp['processor_reference'];
            $apiHash = hash('sha512', $rrr . $apiKey . $this->config->item('remita_merchant_id'));

            $url = $remitaBaseURL . 'remita/exapp/api/v1/send/api/echannelsvc/' . $this->config->item('remita_merchant_id') . '/' . $rrr . '/' . $apiHash . '/status.reg';

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
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
            }
            print_r($responseArray);
        }
        echo 'Update status finished';
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
            //get propertyId from booking information:
            $bookingData = $this->InspectionBooking_model->getById($paymentData['inspection_booking_id']);
            //update property to remove from display
            $this->Property_model->updateById(['active' => 0], $bookingData['property_id']);
        }
    }
}