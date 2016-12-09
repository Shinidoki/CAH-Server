<?php
/**
 * Created by PhpStorm.
 * User: Tony
 * Date: 02.12.2016
 * Time: 10:03
 */

namespace frontend\controllers;


use backend\models\Game;
use backend\models\User;
use yii\web\Controller;
use yii\web\Response;

class CardController extends Controller
{
    public function beforeAction($action)
    {
//        if(!\Yii::$app->request->isAjax){
//            throw new ForbiddenHttpException("This Site is not available for web access!");
//        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionDrawCard()
    {
        $clientToken = \Yii::$app->request->get('clientToken');
        $lobbyId = \Yii::$app->request->get('lobbyId');
        if(empty($clientToken)){
            return $this->errorResponse(["No clienttoken defined!"]);
        }

        /** @var User $user */
        $user = User::find()->where(['generated_id' => $clientToken])->one();
        if(empty($user)){
            return $this->errorResponse(["Invalid Token"]);
        }
        /** @var Game $game */
        $game = Game::find()->where(['game_id' => $lobbyId])->one();
        if(empty($game)){
            return $this->errorResponse(["Invalid Game"]);
        }

        $numberofcards = $user->getGamecards()->count();
        if ($user['is_judge'] = 1){
            $gamecards = $game->getFreeCards()->andWhere(['is_black' => 1])->all();
            $cardstodraw = 1;
        }else{
            $gamecards = $game->getFreeCards()->andWhere(['is_black' => 0])->all();
            $cardstodraw = Game::MAX_CARDS - $numberofcards;
        }

        $card = array_rand($gamecards, $cardstodraw);
        return $card;
    }

    /**
     * The standard form for an error response
     *
     * @param array $error
     * @return array
     */
    private function errorResponse($error = [])
    {
        return [
            'success' => false,
            'errors' => $error
        ];
    }
}