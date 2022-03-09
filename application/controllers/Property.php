<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 
 *
 * @author Temidayo Oluwabusola
 */
require APPPATH . '/libraries/REST_Controller.php';

class Property extends REST_Controller
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
     * Get all property listing
     */
    public function listing_get()
    {
        $data = [];
        $this->load->model('Property_model');

        $data = $this->Property_model->getAvailableActivePublic();

        $this->response(['status' => 'success', 'data' => $data]);
    }
    /**
     * Upload a property listing
     */
    public function listing_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $this->load->model('UserAuth_model');
        $userData = $this->UserAuth_model->getById($userAuthId);
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            $this->load->model('Property_model');
            $data['user_auth_id'] = $userAuthId;
            $data['property_code'] = $this->post('property_code');
            $data['description'] = $this->post('description');
            $data['address'] = $this->post('address');
            $data['state'] = $this->post('state');

            $id = $this->Property_model->insertData($data);
            $this->response(['status' => 'success','data' => ['id' => $id]]);
        }
        else{
            $this->response(['status' => 'fail','message' => 'Please login']);
        }
    }
    public function update_listing_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $this->load->model('UserAuth_model');
        $userData = $this->UserAuth_model->getById($userAuthId);
        if (isset($userData['token']) && $userData['token'] === $loginToken) {
            $this->load->model('Property_model');
            $propertyId = $this->post('id');
            

            $propertyData = $this->Property_model->getById($propertyId);
            if ($propertyData['user_auth_id'] != $userAuthId){
                $this->response(['status' => 'fail','message' => 'Please login']);    
            }

            $data['user_auth_id'] = $userAuthId;
            $data['property_code'] = $this->post('property_code');
            $data['description'] = $this->post('description');
            $data['address'] = $this->post('address');
            $data['state'] = $this->post('state');


            $this->Property_model->updateById($data, $propertyId);
    
            $this->response(['status' => 'success','data' => ['id' => $propertyId]]);
        }
        else{
            $this->response(['status' => 'fail','message' => 'Please login', 'debug' => $userData]);
        }
    }
}
