<?php
namespace App\Controllers;

use App\Models\BudgetModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\TransactionModel;
use App\Models\CategoryModel;
use App\Models\UserModel;
use Throwable;

class Transaction extends ResourceController
{
    use ResponseTrait;

    public function index(){
        $transaction = new TransactionModel();
        $result = $transaction->findAll();
        return $this->respond($result);
    }

    public function show($transaction_id = null){
        $transaction = new TransactionModel();
        $data = $transaction->find($transaction_id);
        if ($data){
            return $this->respond($data);
        }else{
            return $this->failNotFound('cannot find transaction with id => ' . $transaction_id);
        }
    }

    public function create(){
        $transaction = new TransactionModel();
        $transaction_type = $this->request->getPost('transaction_type');
        if (!$transaction_type || !in_array($transaction_type, ['income', 'expense'])) {
            return $this->fail(['message' => 'Invalid transaction_type, expected income or expense']);
        }
        $category = new CategoryModel();
        $category_id = $this->request->getPost('category_id');
        if ($transaction_type === 'expense' && !$category->getCategoryName($category_id)) {
            return $this->failNotFound('Cannot find category with id ' . $category_id. ' or category_id is missing');
        }
        $category = new CategoryModel();

        $user_id = $this->request->getPost('user_id');
        if (!$user_id) {
            return $this->fail(['message' => 'user_id is required']);
        }
        $req_date = $this->request->getPost('date') ?? 'now';
        if (!strtotime($req_date)) {
            return $this->fail(['message' => 'Invalid date format, require format dd-mm-yyyy']);
        }
        $date = date("Y-m-d", strtotime($req_date));

        $note = $this->request->getPost('note');
        $amount = $this->request->getPost('amount');
        if (!$amount) {
            return $this->fail(['message' => 'amount is required']);
        }
        if(!is_numeric($amount)){
            return $this->fail(['message' => 'Invalid amount']);
        }

        $data = array(
            'user_id' => $user_id,
            'transaction_type' => $transaction_type,
            'amount' => $amount,
            'date' => $date,
            'note' => $note,
            'category_id' => $category_id,
        );
        try{
            $result = $transaction->insert($data);
            $data['transaction_id'] = $result;
            $response = array(
                'message'=>'Transaction saved successfully',
                'data'=> $data,
             );
             $user = new UserModel();
             $currentBalance = $user->getBalance($user_id);
             if($transaction_type === 'expense') {
                $budget = new BudgetModel();
                $currentSpend = $budget->getSpend($user_id, $category_id);
                $balance = $currentBalance - $amount;
                $spend = $currentSpend + $amount;
                $budget->setSpend($user_id, $category_id, $spend);
            }else{
                $balance = $currentBalance + $amount;
            } 
            $user->updateBalance($user_id, $balance);

            return $this->respond($response);
        }catch(Throwable $e){
            return $this->fail($e->getMessage());
        }
    }

    public function delete($transaction_id=null){
        $transaction = new TransactionModel();
        $result = $transaction->find($transaction_id);
        if(!$result){
            return $this->failNotFound('No transaction with id ' . $transaction_id);
        }
        $transaction->delete($transaction_id);
        $response = array(
           'status'=> 204,
           'message'=>'Transaction deleted successfully',
        );
        return $this->respondDeleted($response);
    }

    public function getUserTransaction($user_id=null){
        $transaction = new TransactionModel();
        $result = $transaction->getTransactionByUserId($user_id);
        return $this->respond($result);
    }

    public function getUserTransactionByCategory($user_id=null, $category_id=null){
        $transaction = new TransactionModel();
        $result = $transaction->getTransactionByCategory($user_id, $category_id);
        return $this->respond($result);
    }

    public function getUserExpense($user_id=null){
        $transaction = new TransactionModel();
        $result = $transaction->getExpenseByUserId($user_id);
        return $this->respond($result);
    }

    public function getUserIncome($user_id=null){
        $transaction = new TransactionModel();
        $result = $transaction->getIncomeByUserId($user_id);
        return $this->respond($result);
    }
}

