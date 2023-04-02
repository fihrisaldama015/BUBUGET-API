<?php
namespace App\Controllers\Api;

use App\Models\BudgetModel;
use App\Models\CategoryModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use Throwable;

class Budget extends ResourceController
{
    use ResponseTrait;

    public function index(){
        $budget = new BudgetModel();
        $category = new CategoryModel();
        $result = $budget->findAll();
        $data = array();
        foreach ($result as $res){
            $res['category_name'] = $category->getCategoryName($res['category_id']);
            $data[] = $res;

        }
        return $this->respond($data);
    }

    public function show($budget_id=null){
        $budget = new BudgetModel();
        $result = $budget->find($budget_id);
        if(!$result){
            return $this->failNotFound('cannot find budget with id ' . $budget_id);
        }
        $category = new CategoryModel();
        $result['category_name'] = $category->getCategoryName($result['category_id']);
        return $this->respond($result);
    }

    public function create(){
        $user_id = $this->request->getPost('user_id');
        $category_id = $this->request->getPost('category_id');
        $budgetAmount = $this->request->getPost('budget');

        if(!$user_id){
            return $this->fail('user_id is required');
        }
        if(!$category_id){
            return $this->fail('category_id is required');
        }
        if(!$budgetAmount){
            return $this->fail('budget is required');
        }

        $budget = new BudgetModel();
        $category = new CategoryModel();

        $category_name = $category->getCategoryName($category_id);

        if(!$category_name){
            return $this->failNotFound('cannot find category with id ' . $category_id);
        }

        $isCategoryUsed = $budget->isCategoryUsedByUser($user_id, $category_id);

        if($isCategoryUsed){
            return $this->fail('Cannot create a budget with already used category, category_name => '.$category_name.'('.$category_id.')');
        }

        $data = array(
            'user_id'=>$user_id,
            'category_id'=>$category_id,
            'budget'=>$budgetAmount,
            'spend'=>0
        );

        try{
            $result = $budget->insert($data);

            if(!$result){
                return $this->fail('Budget could not be created');
            }

            $data['budget_id'] = $result;
            $response = array(
                'message'=>'Budget added successfully',
                'data'=> $data,
            );

            return $this->respondCreated($response);
        }catch(Throwable $e){
            return $this->fail($e->getMessage());
        }
    }

    public function update($budget_id = null){
        $budgetModel = new BudgetModel();
        $result = $budgetModel->find($budget_id);
        if(!$result){
            return $this->failNotFound('Cannot find budget with id ' . $budget_id);
        }
        $budget = $this->request->getRawInputVar('budget');
        if($budget && !is_numeric($budget)){
            return $this->fail(['message' => 'Invalid budget amount']);
        }
        if(!$budget){
            return $this->fail(['message' => 'Budget is required']);
        }

        $budgetModel->where('budget_id', $budget_id)->set(['budget' => $budget])->update();
        $response = array(
            'status' => 200,
            'message' => 'Budget updated successfully',
            'data' => array(
                'budget_id' => $budget_id,
                'budget' => $budget
            )
        );

        return $this->respondUpdated($response);
    }

    public function delete($budget_id=null){
        $budget = new BudgetModel();
        $result = $budget->find($budget_id);
        if(!$result){
            return $this->failNotFound('Cannot find budget with id ' . $budget_id);
        }
        $budget->delete($budget_id);
        $response = array(
            'status' => 204,
            'message' => 'Budget deleted successfully'
        );
        return $this->respondDeleted($response);
    }

    public function getUserBudget($user_id=null){
        $budget = new BudgetModel();
        $category = new CategoryModel();
        $result = $budget->getBudgetByUser($user_id);
        $data = array();
        foreach ($result as $res){
            $res['category_name'] = $category->getCategoryName($res['category_id']);
            $data[] = $res;

        }
        return $this->respond($data);

    }

    public function getUserBudgetByCategory($user_id=null, $category_id=null){
        $budget = new BudgetModel();
        $category = new CategoryModel();
        $result = $budget->getBudgetByUserCategory($user_id,$category_id);
        $data = array();
        foreach ($result as $res){
            $res['category_name'] = $category->getCategoryName($res['category_id']);
            $data[] = $res;

        }
        return $this->respond($data);

    }
}
