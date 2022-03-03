<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Property_model extends CI_Model {
    private $table = 'property_listings';

    public function __construct() {

        parent::__construct();
        $this->load->database();
    }

    public function insertData($data) {
        $exist = [];
       if (isset($data['id'])) {
            //$exist = $this->getUserById($data['id']);
        }
        if (empty($exist)) {
            $this->db->insert('users', $data);
        }
    }
    public function getAvailableActivePublic() {
        $query = $this->db->query("SELECT * FROM $this->table WHERE 1");
        return $query->result_array();
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
