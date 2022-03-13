<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{

    public function __construct()
    {

        parent::__construct();
        $this->load->database();
        //$this->load->model('Mambu_model');
    }

    public function insertUserData($data)
    {
        $exist = [];
        if (isset($data['id'])) {
            $exist = $this->getUserById($data['id']);
        }
        if (empty($exist)) {
            $this->db->insert('users', $data);
        }
    }
    public function getUserById($id)
    {
        $query = $this->db->query("SELECT * FROM users WHERE id = '$id'");
        return $query->row_array();
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
