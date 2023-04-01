<?php namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model{
    protected $table ="transaction";
    protected $primaryKey = 'transaction_id';
    protected $allowedFields = ['user_id','transaction_type','amount', 'category_id','date','note'];
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getTransactionByUserId($user_id){
        return $this->db->table($this->table)->where('user_id',$user_id)->get()->getResultArray();
    }

    public function getTransactionByCategory($user_id, $category_id){
        return $this->db->table($this->table)->where('user_id',$user_id)->where('category_id',$category_id)->get()->getResultArray();
    }

    public function getExpenseByUserId($user_id){
        return $this->db->table($this->table)->where('user_id',$user_id)->where('transaction_type','expense')->get()->getResultArray();
    }

    public function getIncomeByUserId($user_id){
        return $this->db->table($this->table)->where('user_id',$user_id)->where('transaction_type','income')->get()->getResultArray();
    }
}