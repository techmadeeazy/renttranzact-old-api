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
    }

    public function index_get()
    {
        $this->response(['Welcome']);
    }
    public function reset_password()
    {
        echo 'Reset Password';
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
            if ($userData['blocked'] > 0) {
                $this->response(['status' => 'fail', 'message' => 'Account is blocked. Contact support.']);
            }
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
        //send verification email here
        //initiateEmailVerify($userId, $emailAddress, $fullName)
        $code = $this->initiateEmailVerify($id, $this->post('email_address'), 'User');
        $this->response(['status' => 'success', 'data' => ['user_auth_id' => $id], 'debug' => $code]);
    }

    /**
     * Create a user profile
     */
    public function profile_post()
    {
        $this->load->model('UserProfile_model');
        $data = [
            'user_auth_id' => $this->post('user_auth_id'), 'first_name' => $this->post('first_name'), 'last_name' => $this->post('last_name'), 'phone' => $this->post('phone'), 'gender' => $this->post('gender'), 'address' => $this->post('address'), 'state' => $this->post('state'), 'lga' => $this->post('lga'), 'rc_number' => $this->post('rc_number'), 'company_name' => $this->post('company_name'), 'profile_image_url' => $this->post('profile_image_url')
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
        unset($userAuthData['pwd']);
        $profileData = $this->UserProfile_model->getBy($userAuthId, 'user_auth_id');
        $data = empty($profileData) ? $userAuthData : array_merge($userAuthData, $profileData);
        $this->response(['status' => 'success', 'data' => $data]);
    }

    /**
     * Create a user profile
     */
    public function profile_update_post()
    {
        $this->load->model('UserProfile_model');
        $data = [
            'user_auth_id' => $this->post('user_auth_id'), 'first_name' => $this->post('first_name'), 'last_name' => $this->post('last_name'), 'phone' => $this->post('phone'), 'gender' => $this->post('gender'), 'address' => $this->post('address'), 'state' => $this->post('state'), 'lga' => $this->post('lga'), 'rc_number' => $this->post('rc_number'), 'bank_code' => $this->post('bank_code'), 'bank_account_number' => $this->post('bank_account_number'), 'bank_account_name' => $this->post('bank_account_name'), 'profile_image_url' => $this->post('profile_image_url')
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
            if (empty($propertyData) || $propertyData['status'] != 'approved') {
                $this->response(['status' => 'fail', 'message' => 'Property is not available']);
            }
            $this->load->model('InspectionBooking_model');
            $this->load->model('Base_model');
            //check if booking exist
            $bookingExistData = $this->Base_model->getOneRecord('inspection_bookings', ['inspector_id' => $userAuthId, 'property_id' => $propertyId]);
            if (empty($bookingExistData)) {
                $bookId = $this->InspectionBooking_model->insertData(['inspector_id' => $userAuthId, 'host_id' => $propertyData['user_auth_id'], 'property_id' => $propertyId]);
                $this->load->model('Notification_model');
                //Pause sending of message
                //$this->Notification_model->sendBookingEmail($propertyData);
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
                    if ($b['status'] == 'vetting') {
                        continue;
                    }
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
                $updateData = ['caution_fee' => $this->post('caution_fee'), 'agreed_amount' => $this->post('agreed_amount'), 'status' => 'approve_payment', 'payment_deadline' => $this->post('payment_deadline')];

                $updateData['start_date'] = $this->post('start_date') && strtotime($this->post('start_date')) ? date('Y-m-d', strtotime($this->post('start_date'))) : $bookingData['start_date'];
                $updateData['end_date'] = $this->post('end_date') && strtotime($this->post('end_date')) ? date('Y-m-d', strtotime($this->post('end_date'))) : $bookingData['end_date'];

                $this->InspectionBooking_model->updateById($updateData, $bookingData['id']);
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
                // && strtotime($this->post('start_date'))
                $updateData = ['caution_fee' => $this->post('caution_fee'), 'agreed_amount' => $this->post('agreed_amount'), 'status' => $bookStatus];
                $updateData['start_date'] = $this->post('start_date') && strtotime($this->post('start_date')) ? date('Y-m-d', strtotime($this->post('start_date'))) : $bookingData['start_date'];
                $updateData['end_date'] = $this->post('end_date') && strtotime($this->post('end_date')) ? date('Y-m-d', strtotime($this->post('end_date'))) : $bookingData['end_date'];

                $this->InspectionBooking_model->updateById($updateData, $bookingData['id']);
            }

            $this->response(['status' => 'success', 'message' => 'Booking submitted', 'data' => $this->post()]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        }
        $this->response(['status' => 'fail', 'message' => 'Please login']);
    }

    public function init_password_reset_post()
    {
        $emailAddress = $this->post('email_address');
        $pageData = [];
        $this->load->model('Base_model');
        $result = $this->Base_model->getOneRecord('user_auths', [
            'email_address' =>  $emailAddress
        ]);
        if (!empty($result)) {
            $code = $this->initiatePasswordReset($result['id'], $result['email_address'], 'User');
            $this->response(['status' => "success", 'debug' => $code]);
        }
        $this->response(["status" => "fail", "message" => "Email address is not found"]);
    }

    public function validate_password_reset_post()
    {
        //@TODO: validate email address
        $emailAddress = $this->post('email_address');
        $code = $this->post('reset_code');
        $password = $this->post('password');
        //$confirmPassword = $this->post('confirm_password');

        $this->load->model('Base_model');
        $this->load->model('UserAuth_model');

        $userData = $this->UserAuth_model->getBy($emailAddress, 'email_address');
        if (empty($userData)) {
            $this->response(['status' => "fail", 'message' => 'Invalid account']);
        }


        $resetData = $this->Base_model->getOneRecord('user_password_resets', [
            'user_id' =>  $userData['id'], 'code' => $code
        ]);
        if (empty($resetData)) {
            $this->response(['status' => "fail", 'message' => 'Invalid code. Try again']);
        }
        //update password
        $this->UserAuth_model->setPassword($password, $userData['id']);
        $this->response(["status" => "success", "data" => $resetData]);
    }

    private function initiatePasswordReset($userId, $emailAddress, $fullName)
    {
        $this->load->model('Notification_model');
        $code = rand(10000, 99999);
        $pId = $this->Base_model->add('user_password_resets', ['code' => $code, 'user_id' => $userId]);
        $this->Notification_model->sendPasswordResetEmail($emailAddress, $fullName,  $pId . '-' . $code);
        return $code;
    }

    public function init_email_verify_post()
    {
        $emailAddress = $this->post('email_address');
        $this->load->model('Base_model');
        $result = $this->Base_model->getOneRecord('user_auths', [
            'email_address' =>  $emailAddress
        ]);
        if (!empty($result)) {
            $code = $this->initiateEmailVerify($result['id'], $result['email_address'], 'User');
            $this->response(['status' => "success", 'debug' => $code]);
        }
        $this->response(["status" => "fail", "message" => "Email address is not found"]);
    }
    private function initiateEmailVerify($userId, $emailAddress, $fullName)
    {
        $this->load->model('Base_model');
        $this->load->model('Notification_model');
        $code = rand(10000, 99999);
        $pId = $this->Base_model->add('verify_codes', ['code' => $code, 'user_id' => $userId]);
        $this->Notification_model->sendVerifyEmail($emailAddress, $fullName,  $code);
        return $code;
    }

    public function validate_email_verify_post()
    {
        //@TODO: validate email address
        $emailAddress = $this->post('email_address');
        $code = $this->post('verify_code');

        $this->load->model('Base_model');
        $this->load->model('UserAuth_model');

        $userData = $this->UserAuth_model->getBy($emailAddress, 'email_address');
        if (empty($userData)) {
            $this->response(['status' => "fail", 'message' => 'Invalid account']);
        }


        $resetData = $this->Base_model->getOneRecord('verify_codes', [
            'user_id' =>  $userData['id'], 'code' => $code
        ]);
        if (empty($resetData)) {
            $this->response(['status' => "fail", 'message' => 'Invalid code. Try again']);
        }
        //update password
        $this->UserAuth_model->updateById(['email_verified' => 1], $userData['id']);
        $this->response(["status" => "success", "data" => $resetData]);
    }
    public function review_post()
    {
        $reviewerId = $this->post('reviewer_id');
        $reviewedId = $this->post('reviewed_id');
        $score = $this->post('score');
        $scoreText = $this->post('score_text');
        $this->load->model('Base_model');
        $result = $this->Base_model->add('user_reviews', [
            'reviewer_id' =>  $reviewerId, 'reviewed_id' => $reviewedId, 'score' => $score, 'score_text' => $scoreText, 'created' => date("Y-m-d H:i:s")
        ]);
        $this->response(["status" => "success", "data" => ['id' => $result]]);
    }

    /* public function review_get()
    {
        $this->load->model('Base_model');
        $result = $this->Base_model->get_many('user_reviews');
        $this->response(["status" => "success", "data" =>  $result]);
    }
    */


    public function review_get($userAuthId = '')
    {
        $this->load->model('Base_model');
        $this->load->model('Property_model');
        $this->load->model('UserProfile_model');
        if (empty($userAuthId)) {
            $result = $this->Base_model->get_many('user_reviews');
        } else {
            $result = $this->Base_model->get_many('user_reviews', ['reviewed_id' => $userAuthId]);
        }

        $reviewData = [];
        foreach ($result as $b) {
            $b['reviewer'] = $this->UserProfile_model->getProfile($b['reviewer_id']);
            $b['reviewed'] = $this->UserProfile_model->getProfile($b['reviewed_id']);
            $reviewData[] = $b;
        }

        $this->response(["status" => "success", "data" =>  $reviewData]);
    }


    public function my_favourite_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $propertyId = $this->post('property_id');
        $this->load->model('UserAuth_model');
        $userData = $this->UserAuth_model->getById($userAuthId);
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            $this->load->model('Base_model');
            $pId = $this->Base_model->add('user_favourites', ['user_auth_id' => $userAuthId, 'property_id' => $propertyId, 'created' => date("Y-m-d")]);
            $this->response(["status" => "success", "data" => ['id' => $pId]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        }
    }

    public function my_favourite_get($userAuthId)
    {
        $this->load->model('Base_model');
        $this->load->model('Property_model');
        $this->load->model('PropertyImage_model');
        $this->load->model('UserAuth_model');
        $this->load->model('UserProfile_model');

        $result = $this->Base_model->get_many('user_favourites', ['user_auth_id' => $userAuthId]);
        //$b['property'] = $this->Property_model->getById($b['property_id']);
        $favouriteData = [];
        foreach ($result as $b) {
            $b['property'] = $this->Property_model->getById($b['property_id']);
            $imageData = $this->PropertyImage_model->getFeaturedImage($b['property_id']);
            if (empty($imageData)) {
                //set default values
                $b['property']['image_url'] = 'https://res.cloudinary.com/rent-tranzact-limited/image/upload/v1647366660/bkfn512urnate2dlmxge.jpg';
                $b['property']['image_title'] = '';
            } else {
                $b['property']['image_url'] = $imageData['url'];
                $b['property']['image_title'] = $imageData['title'];
            }
            if (isset($b['property']['user_auth_id'])) { //only get user data if the user_auth_id is not known
                $userAuthData =  $this->UserAuth_model->getById($b['property']['user_auth_id']);
                $profileData = $this->UserProfile_model->getBy($b['property']['user_auth_id'], 'user_auth_id');
                $userData = empty($profileData) ? $userAuthData : array_merge($userAuthData, $profileData);
                unset($userData['address'], $userData['created'], $userData['modified'], $userData['status'], $userData['primary_role'], $userData['id'], $userData['lga'], $userData['rc_number'], $userData['pwd'], $userData['token'], $userData['token_expire'], $userData['referral_code']);
                $b['property']['user'] = $userData;
            }
            $favouriteData[] = $b;
        }

        $this->response(["status" => "success", "data" => $favouriteData]);
    }

    public function remove_my_favourite_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $propertyId = $this->post('property_id');
        $this->load->model('UserAuth_model');
        $userData = $this->UserAuth_model->getById($userAuthId);
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            $this->load->model('Base_model');
            $pId = $this->Base_model->delete('user_favourites', ['user_auth_id' => $userAuthId, 'property_id' => $propertyId]);
            $this->response(["status" => "success", 'message' => 'Favourited property removed successfuly', "data" => ['id' => $pId]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        }
    }
    public function test_email_verify_post()
    {
        $emailAddress = $this->post('email_address');
        $this->load->model('Notification_model');
        $this->Notification_model->testSendEmail($emailAddress);
        $this->response(["status" => "success", "message" => "sent"]);
    }

    public function delete_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $userData = $this->UserAuth_model->getById($userAuthId);
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            $this->load->model('UserAuth_model');
            //strtotime("now +72 hours");
            $this->UserAuth_model->updateById(['deleted' => strtotime("now +72 hours")], $userAuthId);
        }
        $this->response(["status" => "success", 'message' => 'Account deletion will be completed within 72 hours']);
    }
    public function undelete_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $userData = $this->UserAuth_model->getById($userAuthId);
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            $this->load->model('UserAuth_model');
            //strtotime("now +72 hours");
            $this->UserAuth_model->updateById(['deleted' => null], $userAuthId);
        }
        $this->response(["status" => "success", 'message' => 'Account deletion successfully cancelled']);
    }
}
