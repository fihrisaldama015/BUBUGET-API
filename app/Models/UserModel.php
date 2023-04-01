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

    public function updateBalance($user_id, $balance){
        $data = array(
            'balance' => $balance,
        );
        return $this->db->table($this->table)->where('user_id', $user_id)->set($data)->update();
    }

    function getBalance($user_id){
       return  $this->db->table($this->table)->where('user_id', $user_id)->select('balance')->get()->getRow()->balance;
    }
}