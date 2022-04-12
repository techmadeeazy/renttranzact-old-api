<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 
 *
 * @author Temidayo Oluwabusola
 */
require APPPATH . '/libraries/REST_Controller.php';

class Util extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        //$this->load->model('Base_model');
    }


    public function bank_get($bankgId='')
    {
        $this->load->model('Bank_model');
        $bankData = $this->Bank_model->getAll();
        $this->response(['status' => 'success', 'data' => $bankData]);
    }


}
