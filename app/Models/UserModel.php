<?php namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model{
    protected $table ="users";
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['email','user_name','balance'];
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getExpense($user_id){
        $total_expense = 0;
        $transaction = new TransactionModel();
        $list_expense = $transaction->getExpenseByUserId($user_id);
        foreach ($list_expense as $expense){
            $total_expense += $expense['amount'];
        }
        return $total_expense;
    }

    public function getIncome($user_id){
        $total_income = 0;
        $transaction = new TransactionModel();
        $list_income = $transaction->getIncomeByUserId($user_id);
        foreach ($list_income as $income){
            $total_income += $income['amount'];
        }
        return $total_income;
    }

    public function getBalance($userId){
        $expense = $this->getExpense($userId);
        $income = $this->getIncome($userId);
        $balance = $income - $expense;
        return $balance;
    }
}