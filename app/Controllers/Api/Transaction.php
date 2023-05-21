<?php
namespace App\Controllers\Api;

use App\Models\BudgetModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\TransactionModel;
use App\Models\CategoryModel;
use DateTime;
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
        $category = new CategoryModel();

        $transaction_type = $this->request->getVar('transaction_type');
        if (!$transaction_type || !in_array($transaction_type, ['income', 'expense'])) {
            return $this->fail(['message' => 'Invalid transaction_type, expected income or expense']);
        }

        $category_id = $this->request->getVar('category_id');
        if ($transaction_type === 'expense' && !$category->getCategoryName($category_id)) {
            return $this->failNotFound('Cannot find category with id ' . $category_id. ' or category_id is missing');
        }

        $user_id = $this->request->getVar('user_id');
        if (!$user_id) {
            return $this->fail(['message' => 'user_id is required']);
        }

        $date = $this->request->getVar('date') ?? date('Y-m-d') ;
        if (!strtotime($date)) {
            return $this->fail(['message' => 'Invalid date format, require format dd-mm-yyyy']);
        }
        $note = $this->request->getVar('note');

        $amount = $this->request->getVar('amount');
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
             if($transaction_type === 'expense') {
                $budget = new BudgetModel();
                $currentSpend = $budget->getSpend($user_id, $category_id);
                $spend = $currentSpend + $amount;
                $budget->setSpend($user_id, $category_id, $spend);
            }

            return $this->respond($response);
        }catch(Throwable $e){
            return $this->fail($e->getMessage());
        }
    }

    public function update($transaction_id = null){
        $transaction = new TransactionModel();
        $transaction_data = $transaction->find($transaction_id);
        $transaction_type = $this->request->getRawInputVar('transaction_type');
        if(!$transaction_type){
            $transaction_type = $transaction_data['transaction_type'];
        }
        if (!in_array($transaction_type, ['income', 'expense'])) {
            return $this->fail(['message' => 'Invalid transaction_type, expected income or expense']);
        }

        $amount = $this->request->getRawInputVar('amount');
        if(!$amount) {
            return $this->fail(['message' => 'amount is required']);
        }
        if($amount && !is_numeric($amount)){
            return $this->fail(['message' => 'Invalid amount']);
        }

        $category_id = $this->request->getRawInputVar('category_id');
        if($transaction_type === 'expense' && !$category_id){
            $category_id = $transaction_data['category_id'];
        }

        $category = new CategoryModel();
        $categoryName = $category->getCategoryName($category_id);
        if($transaction_type === 'expense' && !$categoryName){
            return $this->failNotFound('Cannot find category with id ' . $category_id. ' or category_id is missing');
        }
        if($transaction_type === 'income' && $categoryName){
            $category_id = null;
        }

        $note = $this->request->getRawInputVar('note');
        if(!$note){
            $note = $transaction_data['note'];
        }

        $date = $this->request->getRawInputVar('date');
        if ($date && !strtotime($date)) {
            return $this->fail(['message' => 'Invalid date format, require format dd-mm-yyyy']);
        }
        if(!$date){
            $date = $transaction_data['date'];
        }

        $data = array(
            'transaction_type' => $transaction_type,
            'amount' => $amount,
            'note' => $note,
            'category_id' => $category_id,
            'date' => $date
        );


        $transaction->update($transaction_id, $data);
        $response = array(
            'status'=> 200,
            'message'=>'Transaction updated successfully',
            'data'=> $data,
        );
        return $this->respond($response);
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
        $result = array();
        $data = $transaction->getTransactionByUserId($user_id);
        $category = new CategoryModel();
        foreach($data as $value){
            $value['category_name'] = $category->getCategoryName($value['category_id']);
            $result[] = $value;
        }
        return $this->respond($result);
    }

    public function getUserTransactionByCategory($user_id=null, $category_id=null){
        $transaction = new TransactionModel();
        $result = $transaction->getTransactionByCategory($user_id, $category_id);
        return $this->respond($result);
    }

}

