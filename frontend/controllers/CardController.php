<?php
/**
 * Created by PhpStorm.
 * User: Tony
 * Date: 02.12.2016
 * Time: 10:03
 */

namespace frontend\controllers;


use backend\models\Game;
use backend\models\Gamecards;
use backend\models\Gameusers;
use backend\models\User;
use yii\helpers\VarDumper;
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
        $gameuser = Gameusers::find()->where(['game_id' => $game->game_id, 'user_id' => $user->user_id]);
        if(empty($gameuser)){
            return $this->errorResponse(["User not in this game"]);
        }
        /** @var Gamecards[] $gamecards */
        $numberofcards = $user->getGamecards()->count();
        if ($user['is_judge'] == 1){
            $gamecards = $game->getFreeCards()->andWhere(['is_black' => 1])->all();
            $cardstodraw = 1;
        }else{
            $gamecards = $game->getFreeCards()->andWhere(['is_black' => 0])->all();
            $cardstodraw = Game::MAX_CARDS - $numberofcards;
        }

        if ($cardstodraw <= 0){
            return [];
        }

        $card = array_rand($gamecards, $cardstodraw);

        if(is_array($card)){
            $usercards = [];
            foreach($card as $crd){
                $usercards[]=$gamecards[$crd];
                $test = \Yii::$app->db->createCommand()->update('cah_gamecards',['user_id'=>$user->user_id],['game_id'=>$game->game_id, 'card_id'=>$gamecards[$crd]->card_id])->execute();
                VarDumper::dump($test,10,true);
            }
            return $usercards;
        }else{
            \Yii::$app->db->createCommand()->update('cah_gamecards',['user_id'=>$user->user_id],['game_id'=>$game->game_id, 'card_id'=>$gamecards[$card]->card_id])->execute();
            return [$gamecards[$card]];
        }
    }

    public function actionGetCurrentCards()
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
        $gameuser = Gameusers::find()->where(['game_id' => $game->game_id, 'user_id' => $user->user_id]);
        if(empty($gameuser)){
            return $this->errorResponse(["User not in this game"]);
        }

        $gamecards = $game->getGamecards()->joinWith('card',false)->select(['cah_card.card_id','text','blanks'])->asArray()->andWhere(['is_black' => $user['is_judge'], 'user_id' => $user->user_id])->all();

        return $gamecards;
    }

    public function actionGetCurrentBlackCard()
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
        $gameuser = Gameusers::find()->where(['game_id' => $game->game_id, 'user_id' => $user->user_id]);
        if(empty($gameuser)){
            return $this->errorResponse(["User not in this game"]);
        }

        $gamecard = $game->getGamecards()->joinWith('card',false)->select(['cah_card.card_id','text','blanks'])->asArray()->andWhere(['is_black' => 1])->andWhere(['IS NOT', 'user_id', NULL])->one();

        return $gamecard;
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