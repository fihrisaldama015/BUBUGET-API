<?php
namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\TransactionModel;
use Firebase\JWT\JWT;
use Google_Client;
use Throwable;

class User extends ResourceController
{
    use ResponseTrait;

    public function index(){
        $user = new UserModel();
        $data = $user->findAll();
        return $this->respond($data);
    }

    public function show($user_id=null){
        $user = new UserModel();
        $data = $user->find($user_id);
        if($data){
            return $this->respond($data);
        }else{
            return $this->failNotFound('cannot find user with id => ' . $user_id);
        }
    }

    public function create(){
        $user_id = $this->request->getPost('user_id');
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if(!$user_id){
            return $this->fail('user_id is required');
        }
        if(!$name){
            return $this->fail('name is required');
        }
        if(!$email){
            return $this->fail('email is required');
        }

        $user = new UserModel();

        $data = array(
            'user_id' => $user_id,
            'user_name' => $name,
            'email' => $email,
            'password' => $password
        );
        try {
            $result = $user->insert($data);

            if(!$result){
                return $this->fail('user failed to create');
            }

            $response = array(
                'message' => 'User created successfully',
                'data' => $data
            );
            return $this->respondCreated($response);
        }catch(Throwable $e){
            return $this->fail($e->getMessage());
        }
    }

    function delete($user_id=null){
        $user = new UserModel();
        $result = $user->find($user_id);
        if(!$result){
            return $this->failNotFound('Cannot found user with id => '.$user_id);
        }
        $user->delete($user_id);
        $response = array(
           'status'=> 204,
           'message'=>'User deleted successfully',
        );
        return $this->respondDeleted($response);
    }

    public function login(){
        $token = $this->request->getVar('token');
        $client_id = getenv('GOOGLE_CLIENT_ID');
        $client = new Google_Client(['client_id' => $client_id]);
        $payload = $client->verifyIdToken($token);
        if($payload){
            $user_id = $payload['sub'];
            $user = new UserModel();
            $data = array(
                'user_id' => $user_id,
                'user_name' => strstr($payload['email'],'@',true),
                'email' => $payload['email'],
            );
            $userAlreadyExist = $user->find($user_id);
            if(!$userAlreadyExist){
                $user->insert($data);
            }
        }else{
            return $this->failUnauthorized('Token is invalid');
        }
        $key = getenv('JWT_SECRET');

        $token = JWT::encode($payload, $key, 'HS256');

        $response = array(
            'token' => $token,
            'message' => 'Login Successfully',
        );

        return $this->respond($response);


    }

    public function getUserStats($user_id=null){
        if(!$user_id){
            return $this->fail('user_id is required');
        }
        
        $user = new UserModel();
        $expense = $user->getExpense($user_id);
        $income = $user->getIncome($user_id);
        $balance = $user->getBalance($user_id);
        
        $response = array(
            'income' => $income,
            'expense' => $expense,
            'balance'=> $balance,
        );
        return $this->respond($response);
    }

    public function getUserExpense($user_id=null){
        if(!$user_id){
            return $this->fail('user_id is required');
        }
        $transaction = new TransactionModel();
        $expense = $transaction->getExpenseByUserId($user_id);
        return $this->respond($expense);
    }

    public function getUserIncome($user_id=null){
        if(!$user_id){
            return $this->fail('user_id is required');
        }
        $transaction = new TransactionModel();
        $income = $transaction->getIncomeByUserId($user_id);
        return $this->respond($income);
    }
}
