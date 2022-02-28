<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {

        parent::__construct();
        $this->load->database();
        //$this->load->model('Mambu_model');
    }

    public function insertUserData($data) {
        $exist = [];
       if (isset($data['id'])) {
            $exist = $this->getUserById($data['id']);
        }
        if (empty($exist)) {
            $this->db->insert('users', $data);
        }
    }
    public function getUserById($id) {
        $query = $this->db->query("SELECT * FROM users WHERE id = '$id'");
        return $query->row_array();
    }
    
    public function insertPaymentData($data) {
        //ensure paymentReference is unique
        $exist = [];
        if (isset($data['reference'])) {
            $exist = $this->getPaymentByReference($data['reference']);
        }
        if (empty($exist)) {
            $this->db->insert('payments', $data);
        }
    }

    public function updatePaymentData($reference, $data) {
        $data['modified'] = date("Y-m-d H:i:s");
        $this->db->where('reference', $reference);
        $this->db->update('payments', $data);
    }
    public function updatePaymentDataWhere($where, $data) {
        $data['modified'] = date("Y-m-d H:i:s");
        $this->db->where($where);
        $this->db->update('payments', $data);
    }

    public function getPaymentByReference($reference) {
        $query = $this->db->query("SELECT * FROM payments WHERE reference = '$reference'");
        return $query->row_array();
    }

    /**
     * Get all rows in the payments table
     * @return array
     */
    public function getPayments() {
        $query = $this->db->query("SELECT * FROM payments WHERE 1");
        return $query->result_array();
    }

    public function rawLog($title, $body) {

        if (is_array($body)) {
            $body = json_encode($body);
        }
        /*
          $sql = $this->db->set(array(
          'title' => $title,
          'body' => $body,
          'created' => date('Y-m-d H:i:s'),
          ))->get_compiled_insert('raw_logs');
         */

        $this->db->insert('raw_logs', array(
            'title' => $title,
            'body' => $body,
            'created' => date('Y-m-d H:i:s'),
        ));
    }

}
