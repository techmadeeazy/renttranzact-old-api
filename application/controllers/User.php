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
        if ($userData['pwd'] == hash('sha1', $this->post('pwd'))) {
            unset($userData['pwd']);
            $userData['token'] = md5(time());
            $userData['token_expire'] = time() + (15 * 60);
            $this->UserAuth_model->updateById(['token' => $userData['token'], 'token_expire' => $userData['token_expire']], $userData['id']);
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
        $data = ['username' => $this->post('username'), 'pwd' => hash('sha1', $this->post('pwd'))];
        //confirm that username does not exist
        $userData = $this->UserAuth_model->getBy($this->post('username'), 'username');
        if (!empty($userData)) {
            $this->response(['status' => 'fail', 'data' => [], 'message' => 'Username already exits']);
        }

        $id = $this->UserAuth_model->insertData($data);
        $this->response(['status' => 'success', 'data' => ['user_auth_id' => $id]]);
    }

    /**
     * Create/Update a user profile
     */
    public function profile_post()
    {
        $this->load->model('UserProfile_model');
        $data = ['user_auth_id' => $this->post('user_auth_id'), 'first_name' => $this->post('first_name'), 'last_name' => $this->post('last_name'), 'email_address' =>  $this->post('last_name'), 'phone' => $this->post('phone'), 'gender' => $this->post('gender')];

        //confirm that profile does not initially exist
        $userProfileData = $this->UserProfile_model->getBy($this->post('user_auth_id'), $this->post('user_auth_id'));

        if (empty($userProfileData)) {
            $id = $this->UserProfile_model->insertData($data);
            $this->response(['status' => 'success', 'message' => 'Profile successfully created', 'data' => ['user_profile_id' => $id]]);
        } else {
            $this->response(['status' => 'success', 'message' => 'Profile exists', 'data' => ['user_profile_id' => $userProfileData['id']]]);
        }
    }

    public function profile_get($userAuthId)
    {
        $this->load->model('UserAuth_model');
        $this->load->model('UserProfile_model');
        $userAuthData =  $this->UserAuth_model->getById($userAuthId);
        $profileData = $this->UserProfile_model->getBy($userAuthId, 'user_auth_id');
        unset($userAuthData['pwd']);
        $this->response(['status' => 'success', 'data' => array_merge($userAuthData, $profileData)]);
    }
}
