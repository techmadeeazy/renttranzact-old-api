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

    public function index_get(){
        $this->response(['Welcome']);
    }

    /**
     * Get all property listing
     */
    public function listing_get(){
        $data = [];
        $this->load->model('Property_model');

        $data = $this->Property_model->getAvailableActivePublic();

        $this->response(['status'=>'success','data' => $data]);
    }
/**
 * Upload a property listing
 */
    public function listing_post(){

    }
}
