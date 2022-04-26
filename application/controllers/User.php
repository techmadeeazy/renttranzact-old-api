<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 
 *
 * @author Temidayo Oluwabusola
 */
require APPPATH . '/libraries/REST_Controller.php';

class User extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        //$this->load->model('Base_model');
    }

    public function index_get()
    {
        $this->response(['Welcome']);
    }

    /**
     * Login with username or password
     */
    public function login_post()
    {
        $this->load->model('UserAuth_model');
        $userData = $this->UserAuth_model->getBy($this->post('username'), 'username');
        if (empty($userData)) {
            $this->response(['status' => 'fail', 'data' => [], 'message' => 'Invalid username']);
        }
        $pwd = $this->post('pwd');
        if (!empty($pwd) && ($userData['pwd'] == hash('sha1', $this->post('pwd')))) {
            unset($userData['pwd']);
            $userData['token'] = md5(time());
            $userData['token_expire'] = time() + (15 * 60);
            $this->UserAuth_model->updateById(['token' => $userData['token'], 'token_expire' => $userData['token_expire']], $userData['id']);
            //get the profile
            $this->load->model('UserProfile_model');
            $userData['profile'] = $this->UserProfile_model->getBy($userData['id'], 'user_auth_id');
            $this->response(['status' => 'success', 'data' => $userData, 'message' => 'login successful']);
        }
        //unset( $userData['pwd']);
        $this->response(['status' => 'fail', 'message' => 'Invalid login']);
    }
    public function refresh_token_post()
    {
        $this->load->model('UserAuth_model');

        $token = $this->post('token');
        $userAuthId = $this->post('user_auth_id');

        $userData = $this->UserAuth_model->getById($userAuthId);
        if (isset($userData['token']) && $userData['token'] === $token) {
            $tokenData = $this->refreshToken($userAuthId);
            $userData['token'] = $tokenData['token'];
            $userData['token_expire'] = $tokenData['token_expire'];
            $this->response(['status' => 'success', 'data' => $userData, 'message' => 'Token refreshed']);
        }
        $this->response(['status' => 'fail', 'message' => 'Token refresh failed']);
    }
    private function refreshToken($userAuthId)
    {
        $this->load->model('UserAuth_model');
        $token = md5(time());
        $tokenExpire =  time() + (15 * 60);
        $this->UserAuth_model->updateById(['token' => $token, 'token_expire' => $tokenExpire], $userAuthId);
        return ['token' => $token, 'token_expire' => $tokenExpire];
    }
    /**
     * Register a user
     */
    public function register_post()
    {
        $this->load->model('UserAuth_model');
        $referralCode = $this->post('referral_code');
        $data = ['account_type' => $this->post('account_type'), 'email_address' => $this->post('email_address'), 'username' => $this->post('username'), 'pwd' => hash('sha1', $this->post('pwd'))];
        
        //confirm that username does not exist
        $userData = $this->UserAuth_model->getBy($this->post('username'), 'username');
        if (!empty($userData)) {
            $this->response(['status' => 'fail', 'data' => [], 'message' => 'Username already exits']);
        }
        $userData = $this->UserAuth_model->getBy($this->post('email_address'), 'email_address');
        if (!empty($userData)) {
            $this->response(['status' => 'fail', 'data' => [], 'message' => 'Email already exits']);
        }
        //confirm that refferrer exists
        if (!empty($referralCode)) {
            $rData = $this->UserAuth_model->getBy($referralCode, 'username');
            if (!empty($rData)) {
                $data['referral_code'] = $referralCode;
            }
        }

        $id = $this->UserAuth_model->insertData($data);
        $this->response(['status' => 'success', 'data' => ['user_auth_id' => $id]]);
    }

    /**
     * Create a user profile
     */
    public function profile_post()
    {
        $this->load->model('UserProfile_model');
        $data = [
            'user_auth_id' => $this->post('user_auth_id'), 'first_name' => $this->post('first_name'), 'last_name' => $this->post('last_name'), 'email_address' =>  $this->post('email_address'), 'phone' => $this->post('phone'), 'gender' => $this->post('gender'), 'address' => $this->post('address'), 'state' => $this->post('state'), 'lga' => $this->post('lga'), 'rc_number' => $this->post('rc_number'), 'company_name' => $this->post('company_name')
        ];

        //confirm that profile does not initially exist
        $userProfileData = $this->UserProfile_model->getBy($this->post('user_auth_id'), 'user_auth_id');

        if (empty($userProfileData)) {
            $id = $this->UserProfile_model->insertData($data);
            $this->response(['status' => 'success', 'message' => 'Profile successfully created', 'data' => ['user_profile_id' => $id]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Profile exists', 'data' => ['user_profile_id' => $userProfileData['id']]]);
        }
    }

    public function profile_get($userAuthId)
    {
        $this->load->model('UserAuth_model');
        $this->load->model('UserProfile_model');
        $userAuthData =  $this->UserAuth_model->getById($userAuthId);
        $profileData = $this->UserProfile_model->getBy($userAuthId, 'user_auth_id');
        $data = empty($profileData) ? $userAuthData : array_merge($userAuthData, $profileData);
        unset($userAuthData['pwd']);
        $this->response(['status' => 'success', 'data' => $data]);
    }

    /**
     * Create a user profile
     */
    public function profile_update_post()
    {
        $this->load->model('UserProfile_model');
        $data = [
            'user_auth_id' => $this->post('user_auth_id'), 'first_name' => $this->post('first_name'), 'last_name' => $this->post('last_name'), 'email_address' =>  $this->post('email_address'), 'phone' => $this->post('phone'), 'gender' => $this->post('gender'), 'address' => $this->post('address'), 'state' => $this->post('state'), 'lga' => $this->post('lga'), 'rc_number' => $this->post('rc_number'), 'bank_code' => $this->post('bank_code'), 'bank_account_number' => $this->post('bank_account_number'), 'bank_account_name' => $this->post('bank_account_name')
        ];

        //update only data that is not empty
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }
        //Get the profile
        $userProfileData = $this->UserProfile_model->getBy($this->post('user_auth_id'), 'user_auth_id');

        if (empty($userProfileData)) {
            $this->response(['status' => 'fail', 'message' => 'Profile to update does not exist. Create the profile first.']);
        }

        $this->UserProfile_model->updateById($data, $userProfileData['id']);
        $data['id'] = $userProfileData['id'];
        $this->response(['status' => 'success', 'message' => 'Profile updated', 'data' => $data]);
    }

    public function book_inspection_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $this->load->model('UserAuth_model');
        $userData = $this->UserAuth_model->getById($userAuthId);
        $propertyId = $this->post('property_id');
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            $this->load->model('Property_model');
            $propertyData = $this->Property_model->getById($propertyId);
            if (empty($propertyData)) {
                $this->response(['status' => 'fail', 'message' => 'Property is not available']);
            }
            $this->load->model('InspectionBooking_model');
            $this->load->model('Base_model');
            //check if booking exist
            $bookingExistData = $this->Base_model->getOneRecord('inspection_bookings', ['inspector_id' => $userAuthId, 'property_id' => $propertyId]);
            if (empty($bookingExistData)) {
                $bookId = $this->InspectionBooking_model->insertData(['inspector_id' => $userAuthId, 'host_id' => $propertyData['user_auth_id'], 'property_id' => $propertyId]);
                $this->response(['status' => 'success', 'message' => 'Booking submitted', 'data' => ['id' => $bookId]]);
            } else {
                $this->response(['status' => 'success', 'message' => 'Booking submitted', 'data' => $bookingExistData]);
            }
        } else {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        }
    }

    public function booking_status_get($role, $userAuthId)
    {
        $this->load->model('UserAuth_model');
        $this->load->model('InspectionBooking_model');
        //$userAuthData =  $this->UserAuth_model->getById($userAuthId);
        $this->load->model('UserProfile_model');


        $bookingResponse = [];
        switch ($role) {
            case 'agent':
                $bookingData = $this->InspectionBooking_model->getAllBy($userAuthId, 'host_id');
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
                $bookingData = $this->InspectionBooking_model->getAllBy($userAuthId, 'inspector_id');
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
        }
        $this->response(['status' => 'success', 'data' => $bookingResponse]);
    }

    public function approve_booking_post()
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
                $this->InspectionBooking_model->updateById(['caution_fee' => $this->post('caution_fee'), 'agreed_amount' => $this->post('agreed_amount'), 'status' => 'approve_payment'], $bookingData['id']);
            }

            $this->response(['status' => 'success', 'message' => 'Booking submitted', 'data' => $this->post()]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        }
        $this->response(['status' => 'fail', 'message' => 'Please login']);
    }

    public function update_booking_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $bookStatus = $this->post('status');

        if (!in_array($bookStatus, ['cancel', 'pending', 'approve_payment'])) {
            $this->response(['status' => 'fail', 'message' => 'Invalid status']);
        }
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
                $this->InspectionBooking_model->updateById(['caution_fee' => $this->post('caution_fee'), 'agreed_amount' => $this->post('agreed_amount'), 'status' => $bookStatus], $bookingData['id']);
            }

            $this->response(['status' => 'success', 'message' => 'Booking submitted', 'data' => $this->post()]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        }
        $this->response(['status' => 'fail', 'message' => 'Please login']);
    }
}
