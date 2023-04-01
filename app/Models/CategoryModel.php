<?php namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model{
    protected $table ="category";
    protected $primaryKey = 'category_id';
    protected $allowedFields = ['category_name'];
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getCategoryName($category_id){
        $result = $this->db->table($this->table)->where('category_id', $category_id)->get()->getRow();
        if($result){
            return $result->category_name;
        }else{
            return false;
        }
    }
}
