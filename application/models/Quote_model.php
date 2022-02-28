<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of Claim_model
 *
 * @author Expertfingers
 */
class Quote_model extends CI_Model
{

    //put your code here
    public function __construct()
    {

        parent::__construct();
        $this->load->database();
        //$this->load->model('Mambu_model');
    }

    public function insertData($data)
    {
        $exist = [];
        $id = 0;
        if (isset($data['tracking_number'])) {
            //$exist = $this->getDataByTrackingNumber($data['tracking_number']);
        }
        if (empty($exist)) {
            $this->db->insert('claims', $data);
            $id = $this->db->insert_id();
        } else {
            $this->updateData($data['tracking_number'], $data);
            $id = $exist['id'];
        }
        return $id;
    }

    public function updateData($trackingNumber, $data)
    {
        $data['modified'] = date("Y-m-d H:i:s");
        $this->db->where('tracking_number', $trackingNumber);
        $this->db->update('claims', $data);
    }

    public function getSingleVehicleMake($id)
    {
        $query = $this->db->query("SELECT id, brand AS make FROM vehicle_brands WHERE id ='$id'");
        return $query->row_array();
    }

    public function getSingleVehicleModel($id)
    {
        $query = $this->db->query("SELECT id, model FROM vehicle_models WHERE  id = '$id'");
        return $query->row_array();
    }

    public function getVehicleMake()
    {
        $query = $this->db->query("SELECT id, brand AS make FROM vehicle_brands WHERE 1");
        return $query->result_array();
    }

    public function getVehicleModel($brandId)
    {
        $query = $this->db->query("SELECT id, model AS make FROM vehicle_models WHERE  brand_id = '$brandId'");
        return $query->result_array();
    }

    public function insertLifeInsuranceQuote($data)
    {
        $this->db->insert('life_insurance_quotes', $data);
        return     $this->db->insert_id();
    }
    public function updateLifeInsuranceQuote($quoteId, $data)
    {

        $data['modified'] = date("Y-m-d H:i:s");
        $this->db->where('id', $quoteId);
        return  $this->db->update('life_insurance_quotes', $data);
    }
    public function insertMotorInsuranceQuote($data)
    {
        $this->db->insert('motor_insurance_quotes', $data);
        return     $this->db->insert_id();
    }

    public function updateMotorInsuranceQuote($quoteId, $data)
    {

        $data['modified'] = date("Y-m-d H:i:s");
        $this->db->where('id', $quoteId);
        return  $this->db->update('motor_insurance_quotes', $data);
    }

    public function updateHomeInsuranceQuote($quoteId, $data)
    {

        $data['modified'] = date("Y-m-d H:i:s");
        $this->db->where('id', $quoteId);
        return  $this->db->update('home_insurance_quotes', $data);
    }

    public function getQuoteData($quoteId, $productCode)
    {
        switch ($productCode) {
            case 'enhanced_term_life':
                $query = $this->db->query("SELECT * FROM life_insurance_quotes WHERE id = '$quoteId'");
                return $query->row_array();
                break;
            case 'motor_insurance':
                $query = $this->db->query("SELECT * FROM motor_insurance_quotes WHERE id = '$quoteId'");
                return $query->row_array();
                break;
            case 'home_insurance':
                $query = $this->db->query("SELECT * FROM home_insurance_quotes WHERE id = '$quoteId'");
                return $query->row_array();
                break;
            case 'travel_insurance':
                $query = $this->db->query("SELECT * FROM travel_insurance_quotes WHERE id = '$quoteId'");
                return $query->row_array();
                break;
            case 'personal_accident':
                $query = $this->db->query("SELECT * FROM personal_accident_quotes WHERE id = '$quoteId'");
                return $query->row_array();
                break;
            case 'income_fund':
                $query = $this->db->query("SELECT * FROM income_fund_quotes WHERE id = '$quoteId'");
                return $query->row_array();
                break;
            case 'money_market_fund':
                $query = $this->db->query("SELECT * FROM money_market_fund_quotes WHERE id = '$quoteId'");
                return $query->row_array();
                break;
            case 'balanced_fund':
                $query = $this->db->query("SELECT * FROM balanced_fund_quotes WHERE id = '$quoteId'");
                return $query->row_array();
                break;
            default:
                return [];
        }
    }

    public function insertAttachment($data)
    {
        $this->db->insert('attachments', $data);
        return     $this->db->insert_id();
    }

    public function insertHomeInsuranceQuote($data)
    {
        $this->db->insert('home_insurance_quotes', $data);
        return     $this->db->insert_id();
    }

    public function insertPersonalAccidentQuote($data)
    {
        $this->db->insert('personal_accident_quotes', $data);
        return     $this->db->insert_id();
    }

    public function updatePersonalAccidentQuote($quoteId, $data)
    {

        $data['modified'] = date("Y-m-d H:i:s");
        $this->db->where('id', $quoteId);
        return  $this->db->update('personal_accident_quotes', $data);
    }

    public function rawLog($title, $body = '')
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        $this->db->insert('raw_logs', array(
            'title' => $title,
            'body' => $body,
            'created' => date('Y-m-d H:i:s'),
        ));
        return     $this->db->insert_id();
    }
}
