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
    public function listing_get($userAuthId = '')
    {
        $data = [];
        $this->load->model('Property_model');
        $this->load->model('PropertyImage_model');
        if (empty($userAuthId)) {
            $propertyData = $this->Property_model->getAvailableActivePublic();
        } else {
            $propertyData = $this->Property_model->getAllBy($userAuthId, 'user_auth_id');
        }

        $responseData = [];
        $this->load->model('UserAuth_model');
        $this->load->model('UserProfile_model');
        foreach ($propertyData as $pd) {
            //get featured image
            $imageData = $this->PropertyImage_model->getFeaturedImage($pd['id']);
            if (empty($userAuthId)) { //only get user data if the user_auth_id is not known
                $userAuthData =  $this->UserAuth_model->getById($pd['user_auth_id']);
                $profileData = $this->UserProfile_model->getBy($pd['user_auth_id'], 'user_auth_id');
                $userData = empty($profileData) ? $userAuthData : array_merge($userAuthData, $profileData);
                unset($userData['address'], $userData['created'], $userData['modified'], $userData['status'], $userData['primary_role'], $userData['id'], $userData['lga'], $userData['rc_number'], $userData['pwd'], $userData['token'], $userData['token_expire'], $userData['referral_code']);
                $pd['user'] = $userData;
            }
            if (empty($imageData)) {
                //set default values
                $pd['image_url'] = 'https://res.cloudinary.com/rent-tranzact-limited/image/upload/v1647366660/bkfn512urnate2dlmxge.jpg';
                $pd['image_title'] = '';
            } else {
                $pd['image_url'] = $imageData['url'];
                $pd['image_title'] = $imageData['title'];
            }

            //get images by property id
            //$pd['images'] = $this->PropertyImage_model->getAllBy($pd['id'], 'property_id');
            $responseData[] = $pd;
        }
        $this->response(['status' => 'success', 'data' => $responseData]);
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
            $data['title'] = $this->post('title');
            $data['amenities'] = $this->post('amenities');
            $data['description'] = $this->post('description');
            $data['address'] = $this->post('address');
            $data['state'] = $this->post('state');
            $data['lga'] = $this->post('lga');
            $data['asking_price'] = $this->post('asking_price');
            $data['active'] = 0;
            $data['status'] = 'pending';
            $data['no_of_parking_lot'] = $this->post('no_of_parking_lot');
            $data['no_of_bedroom'] = $this->post('no_of_bedroom');
            $data['no_of_toilets'] = $this->post('no_of_toilets');
            $data['type'] = $this->post('type');
            $data['display'] = $this->post('display');



            $id = $this->Property_model->insertData($data);
            $this->response(['status' => 'success', 'data' => ['id' => $id]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
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
            if ($propertyData['user_auth_id'] != $userAuthId) {
                $this->response(['status' => 'fail', 'message' => 'Please login']);
            }

            $data['user_auth_id'] = $userAuthId;
            $data['property_code'] = $this->post('property_code');
            $data['description'] = $this->post('description');
            $data['address'] = $this->post('address');
            $data['state'] = $this->post('state');
            $data['asking_price'] = $this->post('asking_price');
            //$data['active'] = 0;
            //$data['status'] = 'available';
            $data['no_of_parking_lot'] = $this->post('no_of_parking_lot');
            $data['no_of_bedroom'] = $this->post('no_of_bedroom');
            $data['no_of_toilets'] = $this->post('no_of_toilets');
            $data['type'] = $this->post('type');
            $data['display'] = strtolower($this->post('display')) == 'public' ? 'public' : 'private';

            $this->Property_model->updateById($data, $propertyId);

            $this->response(['status' => 'success', 'data' => ['id' => $propertyId]]);
        } else {
            $this->response(['status' => 'fail', 'message' => 'Please login', 'debug' => $userData]);
        }
    }
    public function add_image_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $propertyId = $this->post('property_id');
        $images = $this->post('images');

        //$this->load->model('UserAuth_model');
        //$userData = $this->UserAuth_model->getById($userAuthId);
        //if (isset($userData['token']) && $userData['token'] === $loginToken) {
        $this->load->model('Property_model');
        $this->load->model('PropertyImage_model');


        $propertyData = $this->Property_model->getById($propertyId);
        if ($propertyData['user_auth_id'] != $userAuthId) {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        }
        $result = [];
        foreach ($images as $image) {
            $image['property_id'] = $propertyId;
            $id = $this->PropertyImage_model->insertData($image);
            $result[] = array_merge(['id' => $id], $image);
        }

        $this->response(['status' => 'success', 'data' => $result]);
    }

    public function remove_image_post()
    {
        $userAuthId = $this->post('user_auth_id');
        $loginToken = $this->post('token');
        $propertyId = $this->post('property_id');
        $propertyImageId = $this->post('property_image_id');

        //$this->load->model('UserAuth_model');
        //$userData = $this->UserAuth_model->getById($userAuthId);
        //if (isset($userData['token']) && $userData['token'] === $loginToken) {
        $this->load->model('Property_model');
        $this->load->model('PropertyImage_model');
        $this->load->model('Base_model');

        $propertyData = $this->Property_model->getById($propertyId);
        if ($propertyData['user_auth_id'] != $userAuthId) {
            $this->response(['status' => 'fail', 'message' => 'Please login']);
        }
        $result = $this->Base_model->delete('property_images', ['id' => $propertyImageId, 'property_id' => $propertyId]);
        $this->response(['status' => 'success', 'data' => $result]);
    }

    /**
     * Get all property listing
     */
    public function single_listing_get($id)
    {
        $data = [];
        $this->load->model('Property_model');
        $this->load->model('PropertyImage_model');
        $data = $this->Property_model->getById($id);
        $data['images'] = $this->PropertyImage_model->getAllBy($id, 'property_id');
        //add bookings
        $this->load->model('InspectionBooking_model');
        $bookingData = $this->InspectionBooking_model->getAllBy($id, 'property_id');
        $bookingResponse = [];
        if (!empty($bookingData)) {
            $this->load->model('UserProfile_model');
            foreach ($bookingData as $b) {
                $inspectorData = $this->UserProfile_model->getBy($b['inspector_id'], 'user_auth_id');
                unset($inspectorData['address'], $inspectorData['created'], $inspectorData['modified'], $inspectorData['status'], $inspectorData['primary_role'], $inspectorData['id'], $inspectorData['lga'], $inspectorData['rc_number']);
                $b['inspector'] = $inspectorData;

                $hostData = $this->UserProfile_model->getBy($b['host_id'], 'user_auth_id');
                unset($hostData['address'], $hostData['created'], $hostData['modified'], $hostData['status'], $hostData['primary_role'], $hostData['id'], $hostData['lga'], $hostData['rc_number']);
                $b['host'] = $hostData;
                $bookingResponse[] = $b;
            }
        }

        $data['bookings'] = $bookingResponse;

        $this->response(['status' => 'success', 'data' => $data]);
    }

    public function search_post()
    {
        $data = [];
        //state,lga,status
        $keyword = $this->post('keyword');
        $state = $this->post('state');
        $lga = $this->post('lga');
        $this->load->model('Base_model');
        $this->load->model('Property_model');
        $this->load->model('PropertyImage_model');

        $propertyData = $this->Base_model->get_many('property_listings', "description = '%$keyword%' OR title = '%$keyword%' OR state = '$state' OR lga ='$lga'");

        $responseData = [];
        $this->load->model('UserAuth_model');
        $this->load->model('UserProfile_model');
        foreach ($propertyData as $pd) {
            //get featured image
            $imageData = $this->PropertyImage_model->getFeaturedImage($pd['id']);
            if (empty($userAuthId)) { //only get user data if the user_auth_id is not known
                $userAuthData =  $this->UserAuth_model->getById($pd['user_auth_id']);
                $profileData = $this->UserProfile_model->getBy($pd['user_auth_id'], 'user_auth_id');
                $userData = empty($profileData) ? $userAuthData : array_merge($userAuthData, $profileData);
                unset($userData['address'], $userData['created'], $userData['modified'], $userData['status'], $userData['primary_role'], $userData['id'], $userData['lga'], $userData['rc_number'], $userData['pwd'], $userData['token'], $userData['token_expire'], $userData['referral_code']);
                $pd['user'] = $userData;
            }
            if (empty($imageData)) {
                //set default values
                $pd['image_url'] = 'https://res.cloudinary.com/rent-tranzact-limited/image/upload/v1647366660/bkfn512urnate2dlmxge.jpg';
                $pd['image_title'] = '';
            } else {
                $pd['image_url'] = $imageData['url'];
                $pd['image_title'] = $imageData['title'];
            }

            //get images by property id
            //$pd['images'] = $this->PropertyImage_model->getAllBy($pd['id'], 'property_id');
            $responseData[] = $pd;
        }
        $this->response(['status' => 'success', 'data' => $responseData]);
    }

    public function review_post()
    {
        $reviewerId = $this->post('reviewer_id');
        $reviewedId = $this->post('property_id');
        $score = $this->post('score');
        $scoreText = $this->post('score_text');
        $this->load->model('Base_model');
        $result = $this->Base_model->add('property_reviews', [
            'reviewer_id' =>  $reviewerId, 'property_id' => $reviewedId, 'score' => $score, 'score_text' => $scoreText, 'created' => date("Y-m-d H:i:s")
        ]);
        $this->response(["status" => "success", "data" => ['id' => $result]]);
    }

    public function review_get($propertyId = '')
    {
        $this->load->model('Base_model');
        $this->load->model('Property_model');
        $this->load->model('UserProfile_model');
        $this->load->model('PropertyImage_model');

        if (empty($propertyId)) {
            $result = $this->Base_model->get_many('property_reviews');
        } else {
            $result = $this->Base_model->get_many('property_reviews', ['property_id' => $propertyId]);
        }

        $reviewData = [];
        foreach ($result as $b) {
            $b['reviewer'] = $this->UserProfile_model->getProfile($b['reviewer_id']);
            $b['property'] = $this->Property_model->getById($b['property_id']);
            $imageData = $this->PropertyImage_model->getFeaturedImage($b['property_id']);
            if (empty($imageData)) {
                //set default values
                $b['property']['image_url'] = 'https://res.cloudinary.com/rent-tranzact-limited/image/upload/v1647366660/bkfn512urnate2dlmxge.jpg';
                $b['property']['image_title'] = '';
            } else {
                $b['property']['image_url'] = $imageData['url'];
                $b['property']['image_title'] = $imageData['title'];
            }

            $reviewData[] = $b;
        }

        $this->response(["status" => "success", "data" =>  $reviewData]);
    }

    public function payment_history_get($propertyId)
    {
        $bookingResponse = [];
        $this->load->model('Base_model');
        $this->load->model('Property_model');
        $propertyData = $this->Property_model->getById($propertyId);
        $bookingData = $this->Base_model->get_many('inspection_bookings', ['property_id' => $propertyId, 'status' => 'paid']);
        $this->load->model('UserProfile_model');
        foreach ($bookingData as $b) {
            $b['host'] = $this->UserProfile_model->getProfile($b['host_id']);
            $b['inspector'] = $this->UserProfile_model->getProfile($b['inspector_id']);
            $b['property'] = $propertyData;
            $bookingResponse[] = $b;
        }
        //$bookingResponse['property'] = $propertyData; 
        $this->response(['status' => 'success', 'data' => $bookingResponse]);
    }
}
