<?php

/**
 * 
 *
 * @author Temidayo Oluwabusola
 */
require APPPATH . '/libraries/REST_Controller.php';

class Quote extends REST_Controller
{

    public function __construct($config = '')
    {
        parent::__construct();
        $this->load->config($config);
        $this->load->model('Base_model');
        $this->load->model('Policy_model');
        $this->load->model('Quote_model');
    }

    public function motor_insurance_quote_get($type, $productCode, $vehicleValue, $vehicleMake, $email = '', $phone = '', $firstName = '', $lastName = '')
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://staging-api.wapic.com/api/wcgetpremiumquote/' . $type . '/' . $productCode . '/' . $vehicleValue . '/' . $vehicleMake,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;
        $reponseArray = json_decode($response, TRUE);
        if ($reponseArray[0] > 0) {
            $quoteId = $this->Quote_model->insertMotorInsuranceQuote([
                'use_type' => $type, 'product_code' => $productCode,
                'vehicle_value' => $vehicleMake, 'first_name' => $firstName, 'last_name' => $lastName, 'phone' => $phone, 'email_address' => $email
            ]);
            $this->response(['status' => 'success', 'data' => ['quote_id' => $quoteId, 'premium_amount' => $reponseArray[0]]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'The premium amount cannot be retrieved. Please try again later']);
        }
    }

    public function motor_insurance_quote_post()
    {
        $type = $this->post('type');
        $productCode = $this->post('product_code');
        $vehicleValue = $this->post('vehicle_value');
        $vehicleMake = $this->post('vehicle_make');
        $vehicleModel = $this->post('vehicle_model');
        $email = $this->post('email_address');
        $phone = $this->post('phone_number');
        $title = $this->post('title');
        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');
        $yearOfPurchase = $this->post('year_of_purchase');
        $yearOfManufacture = $this->post('year_of_manufacture');
        $engineNumber = $this->post('engine_number');
        $chasisNumber = $this->post('chasis_number');
        $vehicleColour = $this->post('vehicle_colour');
        $registrationNumber = $this->post('registration_number');

        $vehicleMakeString = '';
        $vehicleModelString = '';
        if (!empty($vehicleMake)) {
            $vehicleMakeData = $this->Quote_model->getSingleVehicleMake($vehicleMake);
            $vehicleMakeString = isset($vehicleMakeData['make']) ? $vehicleMakeData['make'] : '';
        }

        if (!empty($vehicleModel)) {
            $vehicleModelData = $this->Quote_model->getSingleVehicleModel($vehicleModel);
            $vehicleModelString = isset($vehicleModelData['model']) ? $vehicleModelData['model'] : '';
        }

        $curl = curl_init();
        $url = 'http://staging-api.coronationinsurance.com.ng/api/wcgetpremiumquote/' . $type . '/' . $productCode . '/' . $vehicleValue . '/' . $vehicleMake;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $reponseArray = json_decode($response, TRUE);
        if (isset($reponseArray[0]) > 0) {
            $quoteId = $this->Quote_model->insertMotorInsuranceQuote([
                'customer_title' => $title, 'use_type' => $type, 'product_code' => $productCode,
                'vehicle_value' => $vehicleValue, 'first_name' => $firstName, 'last_name' => $lastName, 'phone' => $phone,
                'email_address' => $email, 'vehicle_make' => $vehicleMakeString, 'vehicle_model' => $vehicleModelString,
                'vehicle_purchased_year' => $yearOfPurchase, 'vehicle_production_year' => $yearOfManufacture,
                'engine_number' => $engineNumber, 'chasis_number' => $chasisNumber, 'vehicle_colour' => $vehicleColour,
                'premium' => $reponseArray[0]
            ]);
            $this->response(['status' => 'success', 'data' => ['quote_id' => $quoteId, 'premium_amount' => $reponseArray[0]]]);
        } else {
            $this->response([
                'status' => 'fail', 'message' => 'The premium amount cannot be retrieved. Please try again later',
                'debug' => $reponseArray
            ]);
        }
    }

    public function home_insurance_quote_get($insuranceItem, $valueOfInsuranceItem, $email, $phone)
    {
        //validate data
        //difference in date
        if (floatval($valueOfInsuranceItem) > 0) {
            $quoteId = $this->Quote_model->insertHomeInsuranceQuote([
                'phone' => $phone, 'email_address' => $email, 'insurance_item' => $insuranceItem
            ]);
            $this->response(['status' => 'success', 'data' => ['quote_id' => $quoteId, 'premium_amount' => round(0.10 * $valueOfInsuranceItem)]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'The premium amount cannot be retrieved. Please try again later']);
        }
    }

    public function personal_accident_quote_get($sumAssured, $email, $phone)
    {
        //difference in date
        if (intval($sumAssured) > 0) {
            $this->response(['status' => 'success', 'data' => ['premium_amount' => $sumAssured * 0.2]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'The premium amount cannot be retrieved. Please try again later']);
        }
    }

    public function home_insurance_quote_post()
    {

        $insuranceItem = $this->post('insurance_item');
        $valueOfInsuranceItem = $this->post('value_of_insurance_item');
        $email = $this->post('email_address');
        $phone = $this->post('phone');
        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');


        if (floatval($valueOfInsuranceItem) > 0) {
            $quoteId = $this->Quote_model->insertHomeInsuranceQuote([
                'first_name' => $firstName, 'last_name' => $lastName,
                'phone' => $phone, 'email_address' => $email, 'insurance_item' => $insuranceItem
            ]);
            $this->response(['status' => 'success', 'data' => ['quote_id' => $quoteId, 'premium_amount' => round(0.10 * $valueOfInsuranceItem)]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'The premium amount cannot be retrieved. Please try again later']);
        }
    }

    public function personal_accident_quote_post()
    {
        $sumAssured = $this->post('sum_assured');
        $phone = $this->post('phone');
        $email = $this->post('email_address');
        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');

        if (intval($sumAssured) > 0) {

            $quoteId = $this->Quote_model->insertPersonalAccidentQuote([
                'first_name' => $firstName, 'last_name' => $lastName,
                'phone' => $phone, 'email_address' => $email
            ]);
            $this->response(['status' => 'success', 'data' => ['quote_id' => $quoteId, 'premium_amount' => $sumAssured * 0.2]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'The premium amount cannot be retrieved. Please try again later']);
        }
    }

    public function vehicle_make_get()
    {
        $this->response(['status' => 'success', 'data' => $this->Quote_model->getVehicleMake()]);
    }

    public function vehicle_model_get($makeId)
    {
        $this->response(['status' => 'success', 'data' => $this->Quote_model->getVehicleModel($makeId)]);
    }

    public function motor_insurance_use_type_get()
    {
        $this->response(['status' => 'success', 'data' => ['Private', 'Commercial']]);
    }

    /**
     * Get list of motor insurance product code
     */
    public function motor_product_code_get()
    {
        $this->response(['status' => 'success', 'data' => ['1004_IND' => 'Third Party']]);
        //'1008_IND' => 'Third Party(Fire and Theft)','1007_IND' => 'Comprehensive (Prestige)', '1009_IND' => 'Comprehensive (Luxury)'
    }

    /**
     * $quoteType - sum | premium
     */
    public function life_insurance_quote_get($quoteType = 'sum', $amount, $dateOfBirth, $email, $phone)
    {
        //difference in date
        if (intval($amount) > 0) {
            $quoteId = $this->Quote_model->insertLifeInsuranceQuote(['email_address' => $email, 'phone' => $phone, 'date_of_birth' => $dateOfBirth]);
            $this->response(['status' => 'success', 'data' => [
                'quote_id' => $quoteId, 'premium_amount' => $amount * 0.2, 'demise_benefit' => 500000,
                'permanent_disability_benefit' => 500000, 'accidental_medical_expenses_benefit' => 25000, 'date_of_birth' => $dateOfBirth
            ]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'The premium amount cannot be retrieved. Please try again later']);
        }
    }

    /**
     * $quoteType - sum | premium
     */
    public function life_insurance_quote_post()
    {
        $extraData = $this->post();
        $quoteType = $this->post('quote_type');
        $amount = $this->post('amount');
        $phone = $this->post('phone_number');
        $dateOfBirth = $this->post('date_of_birth');
        $email = $this->post('email_address');
        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');
        if ($quoteType == 'sum') {
            $sumAssuredAmount = $amount;
            $premiumAmount = $sumAssuredAmount * 0.01;
            $accidentBenefit = $premiumAmount * 10;
        } else {
            $premiumAmount = $amount;
            $sumAssuredAmount = $premiumAmount * 100;
            $accidentBenefit = $premiumAmount * 10;
        }

        if (intval($amount) > 0) {
            $quoteId = $this->Quote_model->insertLifeInsuranceQuote(['last_name' => $lastName, 'first_name' => $firstName, 'email_address' => $email, 'phone' => $phone, 'date_of_birth' => $dateOfBirth, 'premium' => $premiumAmount, 'sum_assured' => $sumAssuredAmount, 'extra_data' => json_encode($extraData)]);

            $this->response(['status' => 'success', 'data' => [
                'quote_id' => $quoteId, 'premium_amount' => $premiumAmount, 'demise_benefit' => $sumAssuredAmount,
                'permanent_disability_benefit' => $sumAssuredAmount, 'accidental_medical_expenses_benefit' => $accidentBenefit, 
                'date_of_birth' => $dateOfBirth
            ]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'The premium amount cannot be retrieved. Please try again later']);
        }
    }

    public function save_quote_post()
    {
        //$productCode,$quoteId
        $productCode = $this->post('product_code');
        $quoteId = $this->post('quote_id');
        $dataDump = $this->post();
        $this->Quote_model->rawLog($productCode, $dataDump);
        switch ($productCode) {
            case 'enhanced_term_life':
                $data['first_name'] = $this->post('first_name');
                $data['last_name'] = $this->post('last_name');
                $data['gender'] = $this->post('gender');
                $data['email_address'] = $this->post('email_address');
                $data['address'] = $this->post('address');
                $data['beneficiary_first_name'] = $this->post('beneficiary_first_name');
                $data['beneficiary_last_name'] = $this->post('beneficiary_last_name');
                $data['beneficiary_relationship_status'] = $this->post('beneficiary_relationship_status');
                $data['beneficiary_address'] = $this->post('beneficiary_address');
                $data['contingent_first_name'] = $this->post('contingent_first_name');
                $data['contingent_last_name'] = $this->post('contingent_last_name');
                $data['contingent_relationship_status'] = $this->post('contingent_relationship_status');
                $data['contingent_address'] = $this->post('contingent_address');
                foreach ($data as $key => $value) {
                    if (empty($value)) {
                        unset($data[$key]);
                    }
                }
                $debug = $this->Quote_model->updateLifeInsuranceQuote($quoteId, $data);
                break;
            case 'motor_insurance':
                $data['first_name'] = $this->post('first_name');
                $data['last_name'] = $this->post('last_name');
                $data['gender'] = $this->post('gender');
                $data['email_address'] = $this->post('email_address');
                $data['date_of_birth'] = $this->post('date_of_birth');

                foreach ($data as $key => $value) {
                    if (empty($value)) {
                        unset($data[$key]);
                    }
                }
                $debug = $this->Quote_model->updateMotorInsuranceQuote($quoteId, $data);
                break;
            case 'home_insurance':
                $data['first_name'] = $this->post('first_name');
                $data['last_name'] = $this->post('last_name');
                $data['gender'] = $this->post('gender');
                $data['email_address'] = $this->post('email_address');
                $data['date_of_birth'] = $this->post('date_of_birth');

                foreach ($data as $key => $value) {
                    if (empty($value)) {
                        unset($data[$key]);
                    }
                }
                $debug = $this->Quote_model->updateHomeInsuranceQuote($quoteId, $data);
                break;

            case 'personal_accident':
                $data['first_name'] = $this->post('first_name');
                $data['last_name'] = $this->post('last_name');
                $data['gender'] = $this->post('gender');
                $data['email_address'] = $this->post('email_address');
                $data['date_of_birth'] = $this->post('date_of_birth');

                foreach ($data as $key => $value) {
                    if (empty($value)) {
                        unset($data[$key]);
                    }
                }
                $debug = $this->Quote_model->updatePersonalAccidentQuote($quoteId, $data);
                break;

            default:
                $this->response(['status' => 'fail', 'debug' => 'Please provide the product code']);
        }
        if ($debug) {
            $this->response(['status' => 'success', 'message' => 'Data saved successfully']);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Data saving failed. Please try again.']);
        }
    }

    public function get_quote_data_get($quoteId, $productCode)
    {
        //$this->Quote_model->getQuoteData($quoteId, $productCode)
        $this->response(['status' => 'success', 'data' => $this->Quote_model->getQuoteData($quoteId, $productCode)]);
    }

    public function attach_doc_post()
    {
        //$productCode,$quoteId
        $quoteId = $this->post('quote_id');
        $productCode = $this->post('product_code');
        $fileTitle = $this->post('file_title');
        $fileContent = $this->post('file_content');
        $fileType = $this->post('file_type');
        //validate file content
        if (base64_encode(base64_decode($fileContent, true)) === $fileContent) {
            //check file type
            if (!in_array($fileType, ['jpg', 'png', 'jpeg'])) {
                $this->response(['status' => 'fail', 'message' => 'Invalid image type']);
            }
        } else {
            $this->response(['status' => 'fail', 'message' => 'Invalid file']);
        }

        $attachId = $this->Quote_model->insertAttachment(['quote_id' => $quoteId, 'product_code' => $productCode, 'file_title' => $fileTitle, 'file_content' => $fileContent, 'file_type' => $fileType]);
        if ($attachId > 0) {
            $this->response(['status' => 'success', 'message' => 'Data saved successfully', 'data' => ['id' => $attachId]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Data saving failed. Please try again.']);
        }
    }

    public function home_insurance_item_get()
    {
        $this->response(['status' => 'success', 'data' => [
            'Building', 'Laptop', 'Phone', 'Jewelries', 'Wristwatches',
            'Camera', 'Others(Movable Items)', 'Others(Non-Movable Items)'
        ]]);
    }
}
