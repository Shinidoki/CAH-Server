<?php
namespace frontend\controllers;


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
        return [
            'success' => true,
            'clientToken' => md5(mt_rand())
        ];
    }

    public function actionGetLobbyState()
    {
        $lobbyId = \Yii::$app->request->get('lobbyId');

        return [
            'started' => $lobbyId != 1,
            'players' => [
                [
                    'id' => 1,
                    'name' => 'TestUser',
                    'score' => $lobbyId == 1 ? 0 : mt_rand(0,9),
                    'hasPlayed' => false
                ]
            ],
            'settings' => [
                'gameMode' => 1,
                'targetScore' => 10,
                'cardPacks' => [1,6,4],
                'language' => 'DE',
                'kickTimer' => 180
            ]
        ];
    }
}