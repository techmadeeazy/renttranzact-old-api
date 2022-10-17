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
        //$cutOffDate can be set
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
        $this->load->model('Property_model');
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

    public function process_split_fee()
    {
        $this->load->model('InspectionBooking_model');
        $this->load->model('UserAuth_model');

        $pendingSplitFee = $this->InspectionBooking_model->getPendingSplitFee();
        if (empty($pendingSplitFee)) {
            exit('Nothing to process');
        }
        foreach ($pendingSplitFee as $p) {
            print_r($p);
            //Do the calculation
            $rtAgencyCommission = 0.1 * $p['agent_fee']; //10% of rent
            $rtLegalCommission = 0.1 * $p['legal_fee'];
            $rtManagementCommission = 0.1 * $p['management_fee'];
            $rtTotalCommission = $rtAgencyCommission + $rtLegalCommission + $rtManagementCommission + $p['caution_fee'];
            $this->load->model('AdminEarning_model');
            $this->AdminEarning_model->saveData(['property_id' => $p['property_id'], 'agent_fee' => $rtAgencyCommission, 'legal_fee' =>  $rtLegalCommission, 'management_fee' => $rtManagementCommission, 'caution_fee' => $p['caution_fee'], 'total' => $rtTotalCommission]);
            //get host referrer
            $hostData = $this->UserAuth_model->getById($p['host_id']);
            if (!empty($hostData['referral_code'])) {
                echo '<br>A referral found:';
                print_r($hostData);
                $this->load->model('UserWallet_model');
                $this->load->model('UserWalletTransaction_model');
                //get host referrer data
                $hostReferrerData =  $this->UserAuth_model->getByUsername($hostData['referral_code']);
                $hostReferrerCommission = (0.1 * $rtAgencyCommission) + (0.1 * $rtManagementCommission);
                //update wallet
                $this->load->model('UserWallet_model');
                $this->UserWallet_model->saveData(['user_auth_id' => $hostReferrerData['id'], 'available_amount' => $hostReferrerCommission, 'ledger_amount' => $hostReferrerCommission]);
                $this->UserWalletTransaction_model->saveData(['user_auth_id' => $hostReferrerData['id'], 'amount' => $hostReferrerCommission, 'note' => 'Commission from property#' . $p['property_id']]);
            }
            echo '<hr>';
            //Update inspection booking
            $this->InspectionBooking_model->updateById(['split_processed' => 1], $p['id']);
        }
    }
}
