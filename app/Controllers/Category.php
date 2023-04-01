<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CategoryModel;
use Throwable;

class Category extends ResourceController
{

    use ResponseTrait;
    
    public function index(){
        $category = new CategoryModel();
        $data = $category->findAll();
        return $this->respond($data);
    }

    public function show($category_id=null){
        $category = new CategoryModel();
        $data = $category->find($category_id);
        if($data){
            return $this->respond($data);
        }else{
            return $this->failNotFound('cannot find category with id => ' . $category_id);
        }
    }
    
    public function create(){
        $category = new CategoryModel();
        $category_name = $this->request->getPost('category_name');
        if(!$category_name){
            return $this->fail('category_name is required');
        }
        $data = array('category_name' => $category_name);
        try{
            $result = $category->insert($data);
            $data['category_id'] = $result;
            
            $response = array(
                'message'=>'Category added successfully',
                'data'=> $data,
            );

            return $this->respondCreated($response);
        }catch(Throwable $e){
            return $this->fail($e->getMessage());
        }
    
    }
    
    public function delete($category_id=null){
        $category = new CategoryModel();
        $result = $category->find($category_id);
        if(!$result){
            return $this->failNotFound('No category with id => '.$category_id);
        }
        $category->delete($category_id);
        $response = array(
           'status'=> 204,
           'message'=>'Category deleted successfully',
        );
        return $this->respondDeleted($response);
    }
}