<?php

defined('BASEPATH') OR exit('No direct script access allowed');
//require_once(APPPATH . 'third_party/PHPMailer/class.phpmailer.php');
//require_once(APPPATH . 'third_party/PHPMailer/class.smtp.php');
//require_once(APPPATH . 'third_party/PHPMailer/PHPMailerAutoload.php');

class User extends CI_Controller {

    public function __construct() {
        parent::__construct();
        //session_start();
        //$this->load->library(array('session'));
        $this->load->model('Base_model');
        $this->load->model('User_model');
    }

    public function start() {
        $this->load->view('start/index');
    }

    private function runRegisterValidation() {
        //$this->form_validation->set_error_delimiters('<div class="w3-text-red">', '</div>');
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('mobile_number', 'Mobile Number', 'trim|required|min_length[11]|max_length[15]');
        $this->form_validation->set_rules('email_address', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('pwd', 'Password', 'trim|required');
        $this->form_validation->set_rules('confirm_pwd', 'Confirm Password', 'trim|required|matches[pwd]');
        $response = ['status' => 'fail', 'err_msg' => ''];

        if ($this->form_validation->run() == FALSE) {
            $response['err_msg'] = validation_errors();
            return $response;
        }

        $result = $this->Base_model->getOneRecord('users', ['username' => $this->input->post('username')]);
        if (!empty($result)) {
            $response['err_msg'] = 'username exists.';
            return $response;
        }
        $response['data'] = $result;
        $response['status'] = 'success';
        return $response;
    }

    public function register() {
        $pageData = [];
        if ($this->input->post("btnRegister")) {
            $validate = $this->runRegisterValidation();
            if ($validate['status'] == 'fail') {
                $pageData['err_msg'] = $validate['err_msg'];
            } else {
                $data = array('username' => $this->input->post('username'),
                    'first_name' => $this->input->post('first_name'), 'last_name' => $this->input->post('last_name'),
                    'email_address' => ($this->input->post('email_address')),
                    'mobile_number' => $this->input->post('mobile_number'),
                    'pwd' => hash('sha1', $this->input->post('pwd'))
                );

                $this->User_model->insertUserData($data);
                redirect('/user/login');
            }
            //print_r($this->input->post());
        }
        //echo "register";
        $this->load->view('user/meta_link');
        $this->load->view('user/register', $pageData);
    }

    private function runLoginValidation() {
        //$this->form_validation->set_error_delimiters('<div class="w3-text-red">', '</div>');
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('pwd', 'Password', 'trim|required');
        $response = ['status' => 'fail', 'err_msg' => ''];

        if ($this->form_validation->run() == FALSE) {
            $response['err_msg'] = validation_errors();
            return $response;
        }

        $result = $this->Base_model->getOneRecord('users', ['username' => $this->input->post('username')
            , 'pwd' => hash('sha1', $this->input->post('pwd'))]);
        print_r(hash('sha1', $this->input->post('pwd')));
        if (empty($result)) {
            $response['err_msg'] = 'Invalid login';
            return $response;
        }
        $response['data'] = $result;
        $response['status'] = 'success';
        return $response;
    }

    public function login() {
        $pageData = [];
        if ($this->input->post("btnLogin")) {
            $validate = $this->runLoginValidation();
            if ($validate['status'] == 'fail') {
                $pageData['err_msg'] = $validate['err_msg'];
            } else {
                //do session thing here
                //$this->User_model->insertUserData($data);
                redirect('/user/dashboard');
            }
            //print_r($this->input->post());
        }
        $this->load->view('user/meta_link');
        $this->load->view('user/login', $pageData);
        //echo "login";
    }
    public function dashboard(){
        //ensure there's login
        $pageData = [];
        $this->load->view('user/meta_link');
        $this->load->view('user/dashboard', $pageData);
    }
    public function index() {
        redirect('/user/login');
    }

}
