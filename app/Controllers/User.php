<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\TransactionModel;
use Firebase\JWT\JWT;
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
            'balance' => 0
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
        $userModel = new UserModel();

        $uid = $this->request->getPost('uid');
        $email = $this->request->getPost('email');
        $name = $this->request->getPost('displayName');

        $user = $userModel->where('user_id', $uid)->first();
        if(!$user){
            return $this->failNotFound('Cannot found user with uid => '. $uid);
        }
        if(!$email){
            return $this->fail('email is required');
        }
        if(!$name){
            return $this->fail('name is required');
        }

        if($email !== $user['email']){
            return $this->fail('email does not match');
        }

        if($name !== $user['user_name']){
            return $this->fail('name does not match');
        }

        $key = getenv('JWT_SECRET');
        $iat = time();
        $exp = $iat + 3600;

        $payload = array(
            'iss' => 'BUBUGET API',
            'sub' => 'Login Token',
            'iat' => $iat,
            'exp' => $exp,
            'uid' => $uid,
            'email' => $email,
            'name' => $name,

        );

        $token = JWT::encode($payload, $key, 'HS256');

        $response = array(
            'token' => $token,
            'message' => 'Login Successfully',
        );

        return $this->respond($response);


    }

    public function getUserStats($user_id){
        $transaction = new TransactionModel();
        $user = new UserModel();
        $expense = $transaction->getExpenseByUserId($user_id);
        $income = $transaction->getIncomeByUserId($user_id);
        $balance = intval($user->getBalance($user_id));

        $total_expense = 0;
        $total_income = 0;
        
        foreach($expense as $res){
            $total_expense += $res['amount'];
        }

        foreach($income as $res){
            $total_income += $res['amount'];
        }
        
        $response = array(
            'income' => $total_income,
            'expense' => $total_expense,
            'balance'=> $balance
        );
        return $this->respond($response);
    }
}
