<?php
namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Home extends ResourceController
{

    use ResponseTrait;
    
    public function index(){
        $response = array(
            'status' => 200,
            'message'=>'Welcome to the BUBUGET API',
            'documentation' => 'https://documenter.getpostman.com/view/21791853/2s93RQTZfR'
        );
        return $this->respond($response);
    }
}