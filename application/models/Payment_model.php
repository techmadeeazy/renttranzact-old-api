<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payment_model extends CI_Model
{
    private $table = 'payment_transactions';

    public function __construct()
    {

        parent::__construct();
        $this->load->database();
    }

    public function insertData($data)
    {
        $data['created'] = date('Y-m-d H:i:s');
        $data['modified'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    public function getById($id)
    {
        $query = $this->db->query("SELECT * FROM $this->table WHERE id = '$id'");
        return $query->row_array();
    }


    public function updateById($data, $id)
    {
        $data['modified'] = date("Y-m-d H:i:s");
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
    }

    public function getBy($byValue,$by='id')
    {
        $query = $this->db->query("SELECT * FROM $this->table WHERE $by = '$byValue'");
        return $query->row_array();
    }
    
    public function getAll()
    {
        $query = $this->db->query("SELECT * FROM $this->table WHERE 1");
        return $query->result_array();
    }

    public function getPendingPayments()
    {
        $query = $this->db->query("SELECT * FROM $this->table WHERE payment_status = 'pending' ORDER BY id DESC LIMIT 100");
        return $query->result_array();
    }

    public function rawLog($title, $body)
    {

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