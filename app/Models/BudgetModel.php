<?php namespace App\Models;

use CodeIgniter\Model;

class BudgetModel extends Model{
    protected $table ="budget";
    protected $primaryKey = 'budget_id';
    protected $allowedFields = ['user_id','category_id','budget','spend'];
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function isCategoryUsedByUser($user_id, $category_id){
        $used = $this->db->table($this->table)->where('user_id', $user_id)->where('category_id', $category_id)->get()->getRow();
        if($used) return true;
        else return false;
    }

    public function getBudgetByUser($user_id){
        return $this->db->table($this->table)->where('user_id', $user_id)->get()->getResultArray();
    }

    public function getBudgetByUserCategory($user_id, $category_id){
        return $this->db->table($this->table)->where('user_id', $user_id)->where('category_id', $category_id)->get()->getResultArray();
    }

    public function setSpend($user_id, $category_id, $spend){
        $data = array(
            'spend' => $spend,
        );
        return $this->db->table($this->table)->where('user_id',$user_id)->where('category_id',$category_id)->set($data)->update();
    }

    public function getSpend($user_id, $category_id){
        return $this->db->table($this->table)->where('user_id', $user_id)->where('category_id', $category_id)->get()->getRow()->spend;
    }
}