<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController; 
use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    public function index()
    {
        return view('Login');
    }

    public function dashboard(){
        return view('Dashboard');
    }
}
