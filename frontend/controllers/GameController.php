<?php
namespace frontend\controllers;


use backend\models\Card;
use backend\models\Game;
use backend\models\User;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class GameController extends Controller
{
    public function beforeAction($action)
    {
//        if(!\Yii::$app->request->isAjax){
//            throw new ForbiddenHttpException("This Site is not available for web access!");
//        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionAuthenticate()
    {
        $userName = \Yii::$app->request->get('name');

        if(empty($userName)){
            return $this->errorResponse(["No Username defined!"]);
        }

        $newUser = new User();
        $newUser->user_name = $userName;
        if($newUser->save()){
            return [
                'success' => true,
                'clientToken' => $newUser->generated_id
            ];
        } else {
            return $this->errorResponse($newUser->errors);
        }
    }

    public function actionGetLobbies()
    {
        return [
            'success' => true,
            'lobbies' => Game::find()->where(['state' => Game::STATE_INLOBBY])->all()
        ];
    }

    public function actionCreateLobby()
    {
        $clientToken = \Yii::$app->request->get('clientToken');
        $gameName = \Yii::$app->request->get('gameName');

        if(empty($clientToken)){
            return $this->errorResponse(["No clienttoken defined!"]);
        }

        if(!User::isValidToken($clientToken)){
            return $this->errorResponse(["Invalid Token"]);
        }

        $newGame = new Game();
        $newGame->game_name = empty($gameName) ? "CAH Game ".mt_rand() : $gameName;

        if($newGame->save()){
            return [
                'success' => true,
                'lobbyId' => $newGame->game_id
            ];
        } else {
            return $this->errorResponse($newGame->errors);
        }
    }

    public function actionUsers()
    {
        return User::find()->all();
    }

    public function actionGetLobbyState()
    {
        $lobbyId = \Yii::$app->request->get('lobbyId');

        $game = Game::find()->where($lobbyId)->with('cards')->one();

        if(empty($game)){
            return $this->errorResponse(["Lobby not found."]);
        }

        return [
            'success' => true,
            'settings' => $game
        ];
    }

    private function errorResponse($error = [])
    {
        return [
            'success' => false,
            'errors' => $error
        ];
    }
}