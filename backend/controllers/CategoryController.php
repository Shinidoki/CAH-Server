<?php
namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use backend\models\Category;

/**
 * Site controller
 */
class CategoryController extends Controller
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate(){
		\Yii::$app->response->format = Response::FORMAT_JSON;
		$categoryName = \Yii::$app->request->post('categoryName');

		if(!empty($categoryName)){
			$newCategory = new Category();
			$newCategory->name = $categoryName;
			if($newCategory->save()){
				return [
	                'success' => true
	            ];	
			}
		}

		return[
			'success' => false
		];
		
    }
}
