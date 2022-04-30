<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Base_model extends CI_Model
{

    /**
     * Class constructor
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    /**
     * Get all from a table
     * 
     * @access public 
     * @param  $params
     * @return mixed (bool | array)
     */
    public function all($table)
    {

        if (empty($table)) {
            return false;
        }

        $res = $this->db->get($table);
        return $res->result();
    }

    // End func get

    /**
     * Get 
     * 
     * @access public 
     * @param  $params
     * @return mixed (bool | array)
     */
    public function get_count($table, $where = Null)
    {

        if (empty($table)) {
            return false;
        }

        return $this->db->get_where($table, $where)->num_rows();
    }

    /**
     * Get one
     * 
     * @access public 
     * @param  $params
     * @return mixed (bool | array)
     */
    public function getOneRecord($table, $where = Null)
    {
        if (empty($table) || empty($where)) {
            return false;
        }
        $res = $this->db->get_where($table, $where);
        return $res->row_array();
    }

    // End func get

    /**
     * Get many
     * 
     * @access public 
     * @param  $params
     * @return mixed (bool | array)
     */
    public function get_many($table, $where = Null)
    {

        if (empty($table)) {
            return false;
        }

        $res = $this->db->get_where($table, $where);
        return $res->result();
    }

    // End func get

    
    /**
     * undocumented function
     *
     * @return void
     * @author 
     * */
    public function get_sum($row, $table, $where = null)
    {
        $this->db->select_sum($row);
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->get($table)->row();
    }

    /**
     * add
     * 
     * @access public 
     */
    public function add($table, $data, $id = true)
    {

        if (empty($data) or empty($table)) {
            return false;
        }

        $q = $this->db->insert($table, $data);

        if ($id) {
            return $this->db->insert_id();
        } else {
            return $q;
        }
    }

    // End func

    /**
     * edit/update
     * 
     * @access public 
     */
    public function update($table, $data, $where)
    {

        if (empty($data) || empty($table)) {
            return false;
        }
        $this->db->update($table, $data, $where);
        return $this->db->affected_rows() > 0;
    }

    // End func

    /**
     * delete
     * 
     * @access public 
     */
    public function delete($table, $where)
    {
        if (empty($where) or empty($table)) {
            return false;
        }

        $q = $this->db->delete($table, $where);
        return $q;
    }

    // End func

    /**
     * Insert batch data
     * @param $table string 
     * @param $data Array 
     * @access public 
     */
    public function insert_batch($table, $data)
    {
        return $this->db->insert_batch($table, $data);
    }

    /**
     * Return the db object for this model
     * */
    public function get_db()
    {
        return $this->db;
    }
}
