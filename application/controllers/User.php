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
    }
    /**
     * Register a user
     */
    public function register_post()
    {
        $this->load->model('UserAuth_model');
        $data = ['username' => $this->post('username'), 'pwd' => hash('sha1', $this->post('pwd'))];
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
        $id = $this->UserProfile_model->insertData($data);

        $this->response(['status' => 'success', 'data' => ['user_profile_id' => $id]]);
    }

    public function profile_get($userAuthId)
    {
        $this->load->model('UserAuth_model');
        $this->load->model('UserProfile_model');
    $userAuthData =  $this->UserAuth_model->getById($userAuthId);
    $profileData = $this->UserProfile_model->getBy($userAuthId,'user_auth_id');
    unset($userAuthData['pwd']);
    $this->response(['status' => 'success', 'data' => array_merge($userAuthData,$profileData)]);

    }
}
