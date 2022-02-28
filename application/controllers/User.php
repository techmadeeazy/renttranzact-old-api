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
}
