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

        $this->response(['status' => 'success', 'data' => ['amount' => floatval($bookingData['agreed_amount']), 'agent_fee' => ($bookingData['agreed_amount'] * 0.1), 'legal_fee' => ($bookingData['agreed_amount'] * 0.1),'caution_fee' => floatval($bookingData['caution_fee'])]]);
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

            $this->response(['status' => 'success', 'message' => 'Payment started', 'data' => ['reference' => md5(time())]]);
        } 
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        

    }
}
