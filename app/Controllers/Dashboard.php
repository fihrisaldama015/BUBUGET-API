<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController; 
use CodeIgniter\API\ResponseTrait;

class Dashboard extends BaseController
{
    public function index()
    {
        return view('Home');
    }

    public function dashboard(){
        return view('Dashboard');
    }

    public function login(){
        return view('Login');
    }
}
